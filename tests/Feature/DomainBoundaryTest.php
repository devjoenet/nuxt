<?php

declare(strict_types=1);

use App\Actions\CompleteFollowUpTask;
use App\Actions\CreateContact;
use App\Actions\CreateLead;
use App\Actions\UpdateContact;
use App\Actions\UpdateLead;
use App\Data\BusinessSettingData;
use App\Data\ContactData;
use App\Data\FollowUpTaskData;
use App\Data\LeadData;
use App\Data\LeadNoteData;
use App\Enums\FollowUpTaskStatus;
use App\Enums\Permission;
use App\Enums\PermissionGroup;
use App\Enums\RoleName;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\DashboardController;
use App\Http\Requests\Crm\StoreFollowUpTaskRequest;
use App\Http\Requests\Crm\StoreLeadNoteRequest;
use App\Http\Requests\Crm\UpdateContactRequest;
use App\Http\Requests\Crm\UpdateLeadRequest;
use App\Http\Requests\Crm\UpdateOrganizationRequest;
use App\Http\Requests\PublicIntake\StorePublicIntakeRequest;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use App\Http\Resources\CrmRecordOptionResource;
use App\Models\Contact;
use App\Models\CrmActivity;
use App\Models\FollowUpTask;
use App\Models\Lead;
use App\Models\Note;
use App\Models\Organization;
use App\Models\User;
use App\Notifications\FollowUpTaskReminder;
use App\Notifications\NewLeadReceived;
use App\Notifications\QueueFailureDetected;
use App\Policies\ContactPolicy;
use App\Policies\CrmActivityPolicy;
use App\Policies\FollowUpTaskPolicy;
use App\Policies\LeadPolicy;
use App\Policies\NotePolicy;
use App\Policies\OrganizationPolicy;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Response as InertiaResponse;
use SoutheastCode\Audit\Data\ActivityLogFiltersData;
use SoutheastCode\Audit\Policies\ActivityPolicy;
use SoutheastCode\Audit\Queries\ActivityLogQuery;
use SoutheastCode\UserManagement\Data\AdminUserAccessData;
use SoutheastCode\UserManagement\Notifications\UserInvitationNotification;
use Spatie\Activitylog\Models\Activity;
use Symfony\Component\HttpKernel\Exception\HttpException;

beforeEach(function (): void {
    app(RolePermissionSeeder::class)->run();
    $this->withoutVite();
});

test('validated data objects reject values outside their typed contracts', function (): void {
    expect(fn (): AdminUserAccessData => AdminUserAccessData::fromValidated(['role' => 1, 'is_active' => true]))
        ->toThrow(UnexpectedValueException::class);
    expect(fn (): AdminUserAccessData => AdminUserAccessData::fromValidated(['role' => RoleName::Admin->value, 'is_active' => 1]))
        ->toThrow(UnexpectedValueException::class);
    expect(fn (): BusinessSettingData => BusinessSettingData::fromValidated([
        'company_name' => null,
        'notification_email' => 'ops@example.com',
        'default_task_due_days' => 3,
    ]))->toThrow(UnexpectedValueException::class);

    $settings = BusinessSettingData::fromValidated([
        'company_name' => 'Southeast Code',
        'notification_email' => 'ops@example.com',
        'default_task_due_days' => '5',
    ]);

    expect($settings->defaultTaskDueDays)->toBe(5);
    expect(fn (): BusinessSettingData => BusinessSettingData::fromValidated([
        'company_name' => 'Southeast Code',
        'notification_email' => 'ops@example.com',
        'default_task_due_days' => 'five',
    ]))->toThrow(UnexpectedValueException::class);
    expect(fn (): FollowUpTaskData => FollowUpTaskData::fromValidated([
        'title' => null,
        'description' => null,
        'priority' => 'normal',
        'due_at' => now()->addDay()->toDateTimeString(),
    ]))->toThrow(UnexpectedValueException::class);
    expect(fn (): FollowUpTaskData => FollowUpTaskData::fromValidated([
        'title' => 'Call lead',
        'description' => 123,
        'priority' => 'normal',
        'due_at' => now()->addDay()->toDateTimeString(),
    ]))->toThrow(UnexpectedValueException::class);
    $followUp = FollowUpTaskData::fromValidated([
        'title' => 'Call lead',
        'description' => 'Confirm the implementation timeline.',
        'priority' => 'normal',
        'due_at' => now()->addDay()->toDateTimeString(),
    ]);
    expect(fn (): LeadNoteData => LeadNoteData::fromValidated(['body' => null]))
        ->toThrow(UnexpectedValueException::class);

    $filters = ActivityLogFiltersData::fromValidated([
        'search' => 123,
        'event' => 'created',
        'causer' => [1, '2'],
        'subject' => null,
    ]);

    expect($followUp->description)->toBe('Confirm the implementation timeline.')
        ->and($filters->search)->toBeNull()
        ->and($filters->events)->toBe([])
        ->and($filters->causers)->toBe(['2'])
        ->and($filters->subjects)->toBe([])
        ->and(Permission::values())->toContain(Permission::CrmView->value)
        ->and(Permission::CrmView->label())->toBe('Crm View')
        ->and(Permission::CrmView->group())->toBe(PermissionGroup::Crm)
        ->and(Permission::DevelopmentUserTestingManage->groupedLabel())->toBe('User Testing Manage')
        ->and(PermissionGroup::Jobs->label())->toBe('Jobs & queues')
        ->and(StorePublicIntakeRequest::normalize(['budget_range' => 25000]))->toBe(['budget_range' => 25000]);
});

test('activity queries reject invalid direct-call date values', function (): void {
    $filters = new ActivityLogFiltersData(null, [], [], [], 'not-a-date', null);

    expect(fn () => app(ActivityLogQuery::class)->build($filters))
        ->toThrow(UnexpectedValueException::class, 'Invalid activity log date');
});

test('CRM actions handle nullable relationships and protected state transitions', function (): void {
    $admin = User::factory()->create()->assignRole(RoleName::Admin->value);
    $contact = app(CreateContact::class)->handle(new ContactData(
        name: 'No Organization',
        email: 'contact@example.com',
    ), $admin);

    expect($contact->organization_id)->toBeNull();

    $updatedContact = app(UpdateContact::class)->handle($contact, new ContactData(
        name: 'Reassigned Contact',
        email: 'contact@example.com',
        ownerId: null,
        ownerIdWasProvided: true,
    ), $admin);

    expect($updatedContact->owner_id)->toBeNull();

    $lead = app(CreateLead::class)->handle(new LeadData(
        contactId: $updatedContact->uuid,
        status: 'new',
        source: 'referral',
        projectDetails: 'A direct action-created lead without an organization.',
    ), $admin);

    expect($lead->organization_id)->toBeNull();

    $updatedLead = app(UpdateLead::class)->handle($lead, new LeadData(
        contactId: $updatedContact->uuid,
        status: 'qualified',
        source: 'referral',
        projectDetails: 'The lead remains independent of an organization.',
        ownerId: null,
        ownerIdWasProvided: true,
    ), $admin);

    expect($updatedLead->organization_id)->toBeNull()
        ->and($updatedLead->owner_id)->toBeNull();

    $cancelledTask = FollowUpTask::factory()->create([
        'lead_id' => $lead->id,
        'status' => FollowUpTaskStatus::Cancelled,
    ]);

    expect(fn (): FollowUpTask => app(CompleteFollowUpTask::class)->handle($lead, $cancelledTask, $admin))
        ->toThrow(ValidationException::class, 'Only open follow-ups can be completed');

});

test('notification messages expose their delivery metadata and enforce relationship invariants', function (): void {
    $lead = Lead::factory()->create();
    $task = FollowUpTask::factory()->create(['lead_id' => $lead->id]);
    $reminder = new FollowUpTaskReminder($task);
    $reminderMail = $reminder->toMail(new stdClass);

    expect($reminder->via(new stdClass))->toBe(['mail'])
        ->and($reminder->tags())->toContain('follow-up-reminder:'.$task->uuid)
        ->and($reminderMail->subject)->toContain($lead->contact->name);

    $organization = Organization::factory()->create();
    $lead->update(['organization_id' => $organization->id]);
    $newLead = new NewLeadReceived($lead->refresh());
    $newLeadMail = $newLead->toMail(new stdClass);

    expect($newLead->via(new stdClass))->toBe(['mail'])
        ->and($newLead->viaQueues())->toBe(['mail' => 'mail'])
        ->and($newLead->tags())->toContain('lead:'.$lead->uuid)
        ->and(implode(' ', $newLeadMail->introLines))->toContain($organization->name);
    $queueFailure = new QueueFailureDetected('database', 'mail', 'ExampleJob', 'job-123', str_repeat('x', 600));
    $queueMail = $queueFailure->toMail(new stdClass);

    expect($queueFailure->uniqueId())->toBe('queue-failure:job-123')
        ->and($queueFailure->via(new stdClass))->toBe(['mail'])
        ->and($queueFailure->viaQueues())->toBe(['mail' => 'mail'])
        ->and($queueFailure->tags())->toBe(['notifications', 'operations', 'queue-failure'])
        ->and($queueMail->subject)->toBe('Southeast Code queue job failed')
        ->and((new UserInvitationNotification('token'))->viaQueues())->toBe(['mail' => 'mail']);
});

test('policy methods and model relationships are callable across the CRM domain', function (): void {
    $admin = User::factory()->create()->assignRole(RoleName::Admin->value);
    $organization = Organization::factory()->create(['owner_id' => $admin->id]);
    $contact = Contact::factory()->create(['organization_id' => $organization->id, 'owner_id' => $admin->id]);
    $lead = Lead::factory()->create([
        'contact_id' => $contact->id,
        'organization_id' => $organization->id,
        'owner_id' => $admin->id,
    ]);
    $note = Note::factory()->create(['subject_type' => Lead::class, 'subject_id' => $lead->id, 'user_id' => $admin->id]);
    $crmActivity = CrmActivity::factory()->create(['lead_id' => $lead->id, 'user_id' => $admin->id]);
    $task = FollowUpTask::factory()->create(['lead_id' => $lead->id, 'assigned_to' => $admin->id]);
    $activity = Activity::query()->firstOrFail();

    expect(app(ActivityPolicy::class)->view($admin, $activity))->toBeTrue()
        ->and(app(ContactPolicy::class)->view($admin, $contact))->toBeTrue()
        ->and(app(ContactPolicy::class)->delete($admin, $contact))->toBeTrue()
        ->and(app(ContactPolicy::class)->assign($admin, $contact))->toBeTrue()
        ->and(app(CrmActivityPolicy::class)->view($admin, $crmActivity))->toBeTrue()
        ->and(app(CrmActivityPolicy::class)->create($admin))->toBeTrue()
        ->and(app(FollowUpTaskPolicy::class)->view($admin, $task))->toBeTrue()
        ->and(app(FollowUpTaskPolicy::class)->create($admin))->toBeTrue()
        ->and(app(FollowUpTaskPolicy::class)->update($admin, $task))->toBeTrue()
        ->and(app(FollowUpTaskPolicy::class)->delete($admin, $task))->toBeTrue()
        ->and(app(LeadPolicy::class)->view($admin, $lead))->toBeTrue()
        ->and(app(LeadPolicy::class)->assign($admin, $lead))->toBeTrue()
        ->and(app(NotePolicy::class)->view($admin, $note))->toBeTrue()
        ->and(app(NotePolicy::class)->create($admin))->toBeTrue()
        ->and(app(NotePolicy::class)->update($admin, $note))->toBeTrue()
        ->and(app(NotePolicy::class)->delete($admin, $note))->toBeTrue()
        ->and(app(OrganizationPolicy::class)->view($admin, $organization))->toBeTrue()
        ->and(app(OrganizationPolicy::class)->archive($admin, $organization))->toBeTrue()
        ->and(app(OrganizationPolicy::class)->assign($admin, $organization))->toBeTrue();

    expect($crmActivity->lead()->getResults()?->is($lead))->toBeTrue()
        ->and($crmActivity->user()->getResults()?->is($admin))->toBeTrue()
        ->and($task->assignee()->getResults()?->is($admin))->toBeTrue()
        ->and($note->subject()->getResults()?->is($lead))->toBeTrue()
        ->and($note->user()->getResults()?->is($admin))->toBeTrue();
});

test('unauthenticated request boundaries fail closed', function (): void {
    expect((new UpdateContactRequest)->authorize())->toBeFalse()
        ->and((new UpdateOrganizationRequest)->authorize())->toBeFalse()
        ->and((new StoreFollowUpTaskRequest)->authorize())->toBeFalse()
        ->and((new StoreLeadNoteRequest)->authorize())->toBeFalse()
        ->and((new UpdateLeadRequest)->authorize())->toBeFalse();

    expect(fn () => (new ProfileUpdateRequest)->rules())
        ->toThrow(HttpException::class);
    expect(fn () => app(DashboardController::class)(Request::create('/dashboard')))
        ->toThrow(HttpException::class);
});

test('the dormant registration controller remains internally coherent', function (): void {
    $controller = app(RegisteredUserController::class);

    expect($controller->create())->toBeInstanceOf(InertiaResponse::class);

    $request = Request::create('/register', 'POST', [
        'name' => 'Direct Registration',
        'email' => 'direct-registration@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response = $controller->store($request);

    expect($response->getTargetUrl())->toBe(route('dashboard'))
        ->and(auth()->user()?->email)->toBe('direct-registration@example.com');
});

test('unknown CRM option resources retain a stable fallback label', function (): void {
    $resource = new CrmRecordOptionResource((object) ['uuid' => 'unknown-uuid']);
    $organization = Organization::factory()->create(['website' => null]);

    expect($resource->toArray(Request::create('/')))->toBe([
        'label' => 'Unknown record',
        'value' => 'unknown-uuid',
    ])->and((new CrmRecordOptionResource($organization))->toArray(Request::create('/')))->toBe([
        'label' => $organization->name,
        'value' => $organization->uuid,
    ]);
});
