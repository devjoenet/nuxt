<?php

declare(strict_types=1);

use App\Enums\FollowUpTaskStatus;
use App\Enums\LeadStatus;
use App\Models\Contact;
use App\Models\CrmActivity;
use App\Models\FollowUpTask;
use App\Models\Lead;
use App\Models\Note;
use App\Models\Organization;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

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

test('CRM viewers can view a lead with flat resource props', function (): void {
    $user = crmUser();
    $lead = Lead::factory()->create(['owner_id' => $user->id]);

    $this->actingAs($user)
        ->get(route('crm.leads.show', $lead))
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('Crm/Leads/Show')
            ->where('lead.id', $lead->uuid));
});

test('CRM viewers retain visible deleted contact history without exposing hidden contacts', function (): void {
    $user = crmUser();
    $visibleContact = Contact::factory()->create(['owner_id' => $user->id, 'name' => 'Visible Lead Contact']);
    $leadWithVisibleContact = Lead::factory()->create(['contact_id' => $visibleContact->id, 'owner_id' => $user->id]);
    $deletedContact = Contact::factory()->create(['owner_id' => $user->id, 'name' => 'Deleted Lead Contact']);
    $deletedContact->delete();
    $leadWithDeletedContact = Lead::factory()->create(['contact_id' => $deletedContact->id, 'owner_id' => $user->id]);

    $hiddenContact = Contact::factory()->create(['owner_id' => User::factory()->create()->id, 'name' => 'Hidden Lead Contact']);
    $leadWithHiddenContact = Lead::factory()->create(['contact_id' => $hiddenContact->id, 'owner_id' => $user->id]);

    $this->actingAs($user)
        ->get(route('crm.leads.show', $leadWithVisibleContact))
        ->assertInertia(fn (Assert $page): Assert => $page
            ->where('lead.contact.id', $visibleContact->uuid)
            ->where('lead.contact.deleted_at', null));

    $this->actingAs($user)
        ->get(route('crm.leads.show', $leadWithDeletedContact))
        ->assertInertia(fn (Assert $page): Assert => $page
            ->where('lead.contact.id', $deletedContact->uuid)
            ->where('lead.contact.name', 'Deleted Lead Contact')
            ->where('lead.contact.deleted_at', $deletedContact->deleted_at?->toISOString()));

    $this->actingAs($user)
        ->get(route('crm.leads.show', $leadWithHiddenContact))
        ->assertInertia(fn (Assert $page): Assert => $page
            ->where('lead.contact', null)
            ->missing('lead.contact.name'));
});

test('CRM managers can create and update leads', function (): void {
    $user = crmUser(manage: true);
    $user->assignRole(Role::findOrCreate('Admin'));
    $contact = Contact::factory()->create(['owner_id' => $user->id]);
    $organization = Organization::factory()->create(['owner_id' => $user->id]);

    $response = $this->actingAs($user)->post(route('crm.leads.store'), [
        'contact_id' => $contact->uuid,
        'organization_id' => $organization->uuid,
        'owner_id' => (string) $user->id,
        'status' => 'new',
        'source' => 'referral',
        'project_details' => 'Replace manual dispatch reporting.',
    ]);

    $lead = Lead::query()->sole();
    $response->assertRedirect(route('crm.leads.show', $lead));
    expect($lead->contact->is($contact))->toBeTrue()
        ->and($lead->organization?->is($organization))->toBeTrue()
        ->and($lead->owner_id)->toBe($user->id)
        ->and(CrmActivity::query()->count())->toBe(1);

    $this->actingAs($user)->patch(route('crm.leads.update', $lead), [
        'contact_id' => $contact->uuid,
        'organization_id' => $organization->uuid,
        'owner_id' => (string) $user->id,
        'status' => 'qualified',
        'source' => 'referral',
        'project_details' => 'Updated scope.',
    ])->assertSessionHasNoErrors();

    expect($lead->refresh()->status)->toBe(LeadStatus::Qualified)
        ->and($lead->project_details)->toBe('Updated scope.');
});

test('CRM lead selectors conceal inaccessible UUIDs', function (): void {
    $user = crmUser(manage: true);
    $other = User::factory()->create();
    $hiddenContact = Contact::factory()->create(['owner_id' => $other->id]);
    $hiddenOrganization = Organization::factory()->create(['owner_id' => $other->id]);

    $this->actingAs($user)
        ->post(route('crm.leads.store'), [
            'contact_id' => $hiddenContact->uuid,
            'organization_id' => $hiddenOrganization->uuid,
            'status' => 'new',
            'source' => 'direct',
            'project_details' => 'This selector must not reveal another user’s records.',
        ])
        ->assertNotFound();
});

test('CRM option endpoints are visibility-scoped and bounded', function (): void {
    $user = crmUser();
    Contact::factory()->count(21)->create(['owner_id' => $user->id, 'name' => 'Bounded Contact']);
    $hiddenContact = Contact::factory()->create(['owner_id' => User::factory()->create()->id, 'name' => 'Bounded Hidden Contact']);
    Organization::factory()->count(21)->create(['owner_id' => $user->id, 'name' => 'Bounded Organization']);

    $this->actingAs($user)
        ->getJson(route('crm.options.contacts', ['query' => 'Bounded']))
        ->assertSuccessful()
        ->assertJsonCount(20, 'data')
        ->assertJsonMissing(['value' => $hiddenContact->uuid]);

    $this->actingAs($user)
        ->getJson(route('crm.options.organizations', ['query' => 'Bounded']))
        ->assertSuccessful()
        ->assertJsonCount(20, 'data');

    $this->actingAs($user)
        ->getJson(route('crm.options.contacts', ['query' => 'x']))
        ->assertUnprocessable();
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
    $owner = $user;
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
