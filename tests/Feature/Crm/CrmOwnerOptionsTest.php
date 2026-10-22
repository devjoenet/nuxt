<?php

declare(strict_types=1);

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;
use SoutheastCode\Crm\Contacts\Models\Contact;
use SoutheastCode\Crm\Leads\Models\Lead;
use SoutheastCode\Crm\Organizations\Models\Organization;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

test('CRM forms expose inactive current owners while refusing new inactive assignments', function (string $type, string $model, string $prop): void {
    $this->withoutVite();
    $admin = User::factory()->create()->assignRole(Role::findOrCreate('Admin'));
    $admin->givePermissionTo(Permission::findOrCreate('crm.view'), Permission::findOrCreate('crm.manage'));
    $retained = User::factory()->create(['is_active' => false]);
    $other = User::factory()->create(['is_active' => false]);
    $record = $model::factory()->create(['owner_id' => $retained->id]);
    $payload = match ($type) {
        'organizations' => ['name' => 'Client', 'website' => null],
        'contacts' => ['name' => 'Client', 'email' => 'client@example.test', 'phone' => null, 'organization_id' => null],
        'leads' => ['contact_id' => $record->contact->uuid, 'organization_id' => null, 'status' => 'new', 'source' => 'direct', 'project_details' => 'Project'],
    };
    $this->actingAs($admin)->get(route("crm.{$type}.create"))->assertInertia(fn (Assert $page): Assert => $page
        ->where('owners', fn ($owners): bool => collect($owners)->pluck('id')->all() === [$admin->id]));
    $this->get(route("crm.{$type}.edit", $record))->assertInertia(fn (Assert $page): Assert => $page
        ->where('owners', fn ($owners): bool => collect($owners)->contains(fn ($owner): bool => $owner['id'] === $retained->id && $owner['is_active'] === false)));
    $this->get(route("crm.{$type}.index", ['owner' => $retained->id]))->assertInertia(fn (Assert $page): Assert => $page
        ->where('filters.owner', $retained->id)
        ->where('owners', fn ($owners): bool => collect($owners)->pluck('id')->contains($retained->id)));
    $this->post(route("crm.{$type}.store"), [...$payload, 'owner_id' => $other->id])->assertSessionHasErrors('owner_id');
    $this->put(route("crm.{$type}.update", $record), [...$payload, 'owner_id' => $retained->id])->assertSessionHasNoErrors()->assertRedirect();
    expect($record->refresh()->owner_id)->toBe($retained->id);
    $this->put(route("crm.{$type}.update", $record), [...$payload, 'owner_id' => $other->id])->assertSessionHasErrors('owner_id');
    expect($record->refresh()->owner_id)->toBe($retained->id);
    $this->put(route("crm.{$type}.update", $record), [...$payload, 'owner_id' => $admin->id])->assertSessionHasNoErrors()->assertRedirect();
    expect($record->refresh()->owner_id)->toBe($admin->id);
})->with([['organizations', Organization::class, 'organization'], ['contacts', Contact::class, 'contact'], ['leads', Lead::class, 'lead']]);
