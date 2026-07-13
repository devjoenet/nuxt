<?php

declare(strict_types=1);

use App\Enums\FollowUpTaskStatus;
use App\Enums\LeadStatus;
use App\Models\CrmActivity;
use App\Models\FollowUpTask;
use App\Models\Lead;
use App\Models\Note;
use App\Models\Organization;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;

beforeEach(function (): void {
    $this->withoutVite();
});

function crmUser(bool $manage = false): User
{
    $user = User::factory()->create();
    $user->givePermissionTo(Permission::findOrCreate('crm.view'));

    if ($manage) {
        $user->givePermissionTo(Permission::findOrCreate('crm.manage'));
    }

    return $user;
}

test('guests and users without CRM access cannot view leads', function (): void {
    $this->get('/crm/leads')->assertRedirect('/login');
    $this->actingAs(User::factory()->create())->get('/crm/leads')->assertForbidden();
});

test('CRM viewers can search and filter the paginated lead list', function (): void {
    $user = crmUser();
    Lead::factory()->create(['status' => LeadStatus::New]);
    $match = Lead::factory()->create(['status' => LeadStatus::Qualified]);
    $match->contact->update(['name' => 'Searchable Person']);

    $this->actingAs($user)
        ->get(route('crm.leads.index', ['search' => 'Searchable', 'status' => 'qualified']))
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('Crm/Leads/Index')
            ->has('leads.data', 1)
            ->where('leads.data.0.id', $match->uuid));
});

test('CRM managers can create and update leads', function (): void {
    $user = crmUser(manage: true);

    $response = $this->actingAs($user)->post(route('crm.leads.store'), [
        'contact_name' => 'Morgan Lee',
        'contact_email' => 'morgan@example.com',
        'organization_name' => 'Lee Logistics',
        'organization_website' => 'https://lee.example',
        'status' => 'new',
        'source' => 'referral',
        'project_details' => 'Replace manual dispatch reporting.',
    ]);

    $lead = Lead::query()->sole();
    $response->assertRedirect(route('crm.leads.show', $lead));
    expect($lead->contact->name)->toBe('Morgan Lee')
        ->and($lead->organization?->name)->toBe('Lee Logistics')
        ->and(CrmActivity::query()->count())->toBe(1);

    $this->actingAs($user)->patch(route('crm.leads.update', $lead), [
        'contact_name' => 'Morgan Lee',
        'contact_email' => 'morgan@example.com',
        'status' => 'qualified',
        'source' => 'referral',
        'project_details' => 'Updated scope.',
    ])->assertSessionHasNoErrors();

    expect($lead->refresh()->status)->toBe(LeadStatus::Qualified)
        ->and($lead->project_details)->toBe('Updated scope.');
});

test('CRM viewers cannot mutate leads', function (): void {
    $user = crmUser();
    $lead = Lead::factory()->create();

    $this->actingAs($user)->post(route('crm.leads.archive', $lead))->assertForbidden();
    $this->actingAs($user)->post(route('crm.leads.convert', $lead))->assertForbidden();
    $this->actingAs($user)->post(route('crm.leads.notes.store', $lead), ['body' => 'Nope'])->assertForbidden();
});

test('lead conversion is idempotent and completes open follow ups', function (): void {
    $user = crmUser(manage: true);
    $organization = Organization::factory()->create();
    $lead = Lead::factory()->create(['organization_id' => $organization->id]);
    $task = FollowUpTask::factory()->create(['lead_id' => $lead->id]);

    $this->actingAs($user)->post(route('crm.leads.convert', $lead))->assertRedirect();
    $convertedAt = $lead->refresh()->converted_at;
    $this->actingAs($user)->post(route('crm.leads.convert', $lead))->assertRedirect();

    expect($lead->refresh()->converted_at?->equalTo($convertedAt))->toBeTrue()
        ->and($lead->status)->toBe(LeadStatus::Won)
        ->and($organization->refresh()->client_since_at)->not->toBeNull()
        ->and($task->refresh()->status)->toBe(FollowUpTaskStatus::Completed)
        ->and(CrmActivity::query()->where('type', 'lead_converted')->count())->toBe(1);
});

test('managers can add notes and complete a task belonging to the lead', function (): void {
    $user = crmUser(manage: true);
    $lead = Lead::factory()->create();
    $task = FollowUpTask::factory()->create(['lead_id' => $lead->id]);

    $this->actingAs($user)->post(route('crm.leads.notes.store', $lead), ['body' => 'Client prefers email.'])->assertRedirect();
    $this->actingAs($user)->post(route('crm.leads.tasks.complete', [$lead, $task]))->assertRedirect();

    expect(Note::query()->sole()->body)->toBe('Client prefers email.')
        ->and($task->refresh()->status)->toBe(FollowUpTaskStatus::Completed);
});

test('managers can create a follow-up assigned to the lead owner', function (): void {
    $user = crmUser(manage: true);
    $owner = User::factory()->create();
    $lead = Lead::factory()->create(['owner_id' => $owner->id]);

    $this->actingAs($user)->post(route('crm.leads.tasks.store', $lead), [
        'title' => 'Confirm discovery call',
        'priority' => 'high',
        'due_at' => now()->addDay()->toDateTimeString(),
    ])->assertSessionHasNoErrors();

    $task = FollowUpTask::query()->sole();
    expect($task->assigned_to)->toBe($owner->id)
        ->and($task->title)->toBe('Confirm discovery call');
});

test('follow-up scopes distinguish overdue and due-soon work', function (): void {
    FollowUpTask::factory()->create(['due_at' => now()->subDay()]);
    FollowUpTask::factory()->create(['due_at' => now()->addDay()]);
    FollowUpTask::factory()->create(['due_at' => now()->addWeek()]);

    expect(FollowUpTask::query()->overdue()->count())->toBe(1)
        ->and(FollowUpTask::query()->dueSoon()->count())->toBe(1);
});
