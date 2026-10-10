<?php

declare(strict_types=1);

use App\Models\Contact;
use App\Models\DevelopmentTask;
use App\Models\Organization;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Permission;

beforeEach(function (): void {
    $this->withoutVite();
});

dataset('history records', [
    [Contact::class, 'crm.contacts', 'owner_id'],
    [Organization::class, 'crm.organizations', 'owner_id'],
    [DevelopmentTask::class, 'development.tasks', 'submitter_id'],
]);

test('record history requires both record access and audit permission', function (string $model, string $prefix, string $owner): void {
    $user = User::factory()->create();
    $user->givePermissionTo(Permission::findOrCreate('crm.view'), Permission::findOrCreate('audit.view'));
    $record = $model::factory()->create([$owner => $user->id]);
    $url = route("{$prefix}.history", $record);
    $this->get($url)->assertRedirect('/login');
    $this->actingAs($user)->get($url)->assertOk();
    $other = User::factory()->create();
    $other->givePermissionTo(Permission::findOrCreate('crm.view'), Permission::findOrCreate('audit.view'));
    $this->actingAs($other)->get($url)->assertStatus($model === DevelopmentTask::class ? 403 : 404);
    $user->revokePermissionTo('audit.view');
    $this->actingAs($user)->get($url)->assertForbidden();
    $this->get(route("{$prefix}.show", $record))->assertInertia(fn (Assert $page): Assert => $page->where('canViewHistory', false));
})->with('history records');

test('record history isolates subjects paginates and preserves archived record provenance', function (string $model, string $prefix, string $owner): void {
    $user = User::factory()->create();
    $user->givePermissionTo(Permission::findOrCreate('crm.view'), Permission::findOrCreate('audit.view'));
    $record = $model::factory()->create([$owner => $user->id]);
    $other = $model::factory()->create([$owner => $user->id]);
    Activity::query()->delete();
    foreach (range(1, 16) as $number) {
        activity()->performedOn($record)->causedBy($user)->event('updated')->log("Change {$number}");
    }
    $latest = Activity::query()->latest('id')->firstOrFail();
    $latest->update(['attribute_changes' => ['old' => ['status' => 'old'], 'attributes' => ['status' => 'new']]]);
    activity()->performedOn($other)->log('Unrelated record');
    activity()->performedOn($user)->log('Unrelated subject type');
    $this->actingAs($user)->get(route("{$prefix}.history", $record))->assertInertia(fn (Assert $page): Assert => $page
        ->component('History/Show')->where('backUrl', route("{$prefix}.show", $record))
        ->where('activities.total', 16)->has('activities.data', 15)
        ->where('activities.data.0.id', $latest->id)->where('activities.data.0.actor', $user->name)
        ->where('activities.data.0.changes.old.status', 'old'));
    $this->get(route("{$prefix}.history", [$record, 'page' => 2]))->assertInertia(fn (Assert $page): Assert => $page->has('activities.data', 1));
    Activity::query()->delete();
    $this->get(route("{$prefix}.history", $record))->assertInertia(fn (Assert $page): Assert => $page->has('activities.data', 0));
    $record->delete();
    $this->get(route("{$prefix}.history", $record))->assertNotFound();
    expect(Activity::query()->where('subject_type', $record->getMorphClass())->where('subject_id', $record->getKey())->exists())->toBeTrue();
})->with('history records');
