<?php

declare(strict_types=1);

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;
use SoutheastCode\Crm\FollowUps\Models\FollowUpTask;
use SoutheastCode\Crm\Leads\Models\Lead;
use SoutheastCode\Crm\Shared\Models\Note;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Permission;

beforeEach(function (): void {
    $this->withoutVite();
});

function historyViewer(): User
{
    $user = User::factory()->create();
    $user->givePermissionTo(Permission::findOrCreate('crm.view'), Permission::findOrCreate('audit.view'));

    return $user;
}

test('history enforces authentication audit permission and lead visibility', function (): void {
    $user = historyViewer();
    $lead = Lead::factory()->create(['owner_id' => $user->id]);
    $url = route('crm.leads.history', $lead);
    $this->get($url)->assertRedirect('/login');
    $this->actingAs(User::factory()->create())->get($url)->assertForbidden();
    $this->actingAs(historyViewer())->get($url)->assertNotFound();
    $user->revokePermissionTo('audit.view');
    $this->actingAs($user)->get($url)->assertForbidden();
    $this->get(route('crm.leads.show', $lead))->assertInertia(fn (Assert $page): Assert => $page->where('canViewHistory', false));
});

test('history includes related deleted audits and excludes unrelated records and matching numeric ids', function (): void {
    $user = historyViewer();
    $lead = Lead::factory()->create(['owner_id' => $user->id]);
    $other = Lead::factory()->create();
    $note = Note::factory()->create(['subject_type' => $lead->getMorphClass(), 'subject_id' => $lead->id]);
    $task = FollowUpTask::factory()->create(['lead_id' => $lead->id]);
    Note::factory()->create(['subject_type' => $other->getMorphClass(), 'subject_id' => $other->id]);
    FollowUpTask::factory()->create(['lead_id' => $other->id]);
    activity()->performedOn($lead)->causedBy($user)->event('updated')->withProperties([])->log('Lead updated');
    $leadAudit = Activity::query()->latest('id')->firstOrFail();
    $leadAudit->update(['attribute_changes' => ['old' => ['status' => 'new'], 'attributes' => ['status' => 'qualified']]]);
    $note->delete();
    $task->delete();
    $expected = Activity::query()->where(function ($query) use ($lead, $note, $task): void {
        $query->where(fn ($query) => $query->where('subject_type', $lead->getMorphClass())->where('subject_id', $lead->id))
            ->orWhere(fn ($query) => $query->where('subject_type', $note->getMorphClass())->where('subject_id', $note->id))
            ->orWhere(fn ($query) => $query->where('subject_type', $task->getMorphClass())->where('subject_id', $task->id));
    })->orderByDesc('created_at')->orderByDesc('id')->pluck('id')->all();
    activity()->performedOn($user)->log('Unrelated actor record');
    $this->actingAs($user)->get(route('crm.leads.history', $lead))
        ->assertOk()->assertInertia(fn (Assert $page): Assert => $page
        ->component('crm::Leads/History', false)->where('lead.id', $lead->uuid)
        ->where('activities.total', count($expected))
        ->where('activities.data', fn ($rows): bool => array_column($rows->toArray(), 'id') === $expected));
    $note->forceDelete();
    $task->forceDelete();
    $this->get(route('crm.leads.history', $lead))->assertInertia(fn (Assert $page): Assert => $page->where('activities.total', count($expected) + 2));
});

test('history paginates deterministically and returns recorded actor and changes', function (): void {
    $user = historyViewer();
    $lead = Lead::factory()->create(['owner_id' => $user->id]);
    Activity::query()->delete();
    foreach (range(1, 16) as $number) {
        activity()->performedOn($lead)->causedBy($user)->event('updated')->log("Change {$number}");
    }
    $latest = Activity::query()->latest('id')->firstOrFail();
    $latest->update(['attribute_changes' => ['old' => ['status' => 'new'], 'attributes' => ['status' => 'qualified']]]);
    $this->actingAs($user)->get(route('crm.leads.history', $lead))->assertInertia(fn (Assert $page): Assert => $page
        ->has('activities.data', 15)->where('activities.total', 16)->where('activities.last_page', 2)
        ->where('activities.data.0.id', $latest->id)->where('activities.data.0.actor', $user->name)
        ->where('activities.data.0.action', 'Updated')->where('activities.data.0.changes.old.status', 'new')
        ->where('activities.data.0.changes.attributes.status', 'qualified'));
    $this->get(route('crm.leads.history', [$lead, 'page' => 2]))->assertInertia(fn (Assert $page): Assert => $page->has('activities.data', 1));
    Activity::query()->delete();
    $this->get(route('crm.leads.history', $lead))->assertInertia(fn (Assert $page): Assert => $page->has('activities.data', 0)->where('activities.total', 0));
});

test('history recovers missing related records only when audit evidence establishes the association', function (): void {
    $user = historyViewer();
    $lead = Lead::factory()->create(['owner_id' => $user->id]);
    Activity::query()->delete();
    $noteAudit = Activity::query()->create([
        'subject_type' => (new Note)->getMorphClass(), 'subject_id' => 9001,
        'description' => 'deleted', 'event' => 'deleted',
        'attribute_changes' => ['old' => ['subject_type' => $lead->getMorphClass(), 'subject_id' => $lead->id]],
    ]);
    $taskAudit = Activity::query()->create([
        'subject_type' => (new FollowUpTask)->getMorphClass(), 'subject_id' => 9002,
        'description' => 'deleted', 'event' => 'deleted',
        'attribute_changes' => ['old' => ['lead_id' => $lead->id]],
    ]);
    Activity::query()->create([
        'subject_type' => (new Note)->getMorphClass(), 'subject_id' => 9003,
        'description' => 'Unknown association', 'event' => 'deleted',
    ]);
    $this->actingAs($user)->get(route('crm.leads.history', $lead))->assertInertia(fn (Assert $page): Assert => $page
        ->where('activities.total', 2)
        ->where('activities.data.0.id', $taskAudit->id)
        ->where('activities.data.1.id', $noteAudit->id)
        ->where('activities.data.0.actor', 'System'));
});
