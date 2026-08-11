<?php

declare(strict_types=1);

use App\Enums\DevelopmentNoteAudience;
use App\Enums\DevelopmentRequestType;
use App\Enums\DevelopmentTaskArea;
use App\Enums\DevelopmentTaskStatus;
use App\Enums\Permission as DevelopmentPermission;
use App\Models\DevelopmentTask;
use App\Models\DevelopmentTaskNote;
use App\Models\User;
use App\Notifications\DevelopmentTaskClarificationAdded;
use App\Notifications\DevelopmentTaskInformationRequested;
use App\Notifications\DevelopmentTaskPromoted;
use App\Notifications\DevelopmentTaskRejected;
use Illuminate\Support\Facades\Notification;
use Inertia\Testing\AssertableInertia;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

function developmentWorkflowUser(
    DevelopmentPermission $permission,
    string $roleName,
): User {
    $role = Role::findOrCreate($roleName, 'web');
    $role->givePermissionTo(Permission::findOrCreate($permission->value, 'web'));
    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

function validDevelopmentRequest(array $overrides = []): array
{
    return [
        'title' => 'Improve release visibility',
        'request_type' => DevelopmentRequestType::Improvement->value,
        'affected_area' => DevelopmentTaskArea::Development->value,
        'description' => 'Release status is currently spread across multiple records.',
        'desired_outcome' => 'Give requesters one current view of release progress.',
        'acceptance_criteria' => "The current status is visible.\nInternal planning notes remain private.",
        ...$overrides,
    ];
}

test('requesters can submit and view only their own active requests', function (): void {
    $requester = developmentWorkflowUser(
        DevelopmentPermission::DevelopmentRequestsCreate,
        'Workflow Requester',
    );
    $otherTask = DevelopmentTask::factory()->create();

    $this->actingAs($requester)
        ->get(route('development.requests.create'))
        ->assertSuccessful()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->component('Development/Requests/Create')
            ->has('requestTypes', count(DevelopmentRequestType::cases()))
            ->has('affectedAreas', count(DevelopmentTaskArea::cases())));

    $response = $this->actingAs($requester)
        ->post(route('development.requests.store'), validDevelopmentRequest());

    $task = DevelopmentTask::query()->whereBelongsTo($requester, 'submitter')->sole();

    $response->assertRedirect(route('development.tasks.show', $task));
    expect($task->status)->toBe(DevelopmentTaskStatus::Submitted)
        ->and($task->title)->toBe('Improve release visibility');

    $this->actingAs($requester)
        ->get(route('development.requests.index'))
        ->assertSuccessful()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->component('Development/Requests/Index')
            ->has('requests.data', 1)
            ->where('requests.data.0.id', $task->uuid));

    $this->actingAs($requester)
        ->get(route('development.tasks.show', $otherTask))
        ->assertForbidden();
});

test('development request submission validates and ignores requester priority', function (): void {
    $requester = developmentWorkflowUser(
        DevelopmentPermission::DevelopmentRequestsCreate,
        'Validated Workflow Requester',
    );

    $this->actingAs($requester)
        ->post(route('development.requests.store'), validDevelopmentRequest([
            'title' => '   ',
            'request_type' => 'invented',
            'priority' => 'high',
        ]))
        ->assertSessionHasErrors(['title', 'request_type']);

    expect(DevelopmentTask::query()->whereBelongsTo($requester, 'submitter')->exists())->toBeFalse();
});

test('review queue contains only requests in review-stage statuses', function (): void {
    $reviewer = developmentWorkflowUser(
        DevelopmentPermission::DevelopmentRequestsReview,
        'Workflow Reviewer',
    );
    $submitted = DevelopmentTask::factory()->create(['submitted_at' => now()->subMinutes(3)]);
    $needsInformation = DevelopmentTask::factory()->needsInformation()->create(['submitted_at' => now()->subMinutes(2)]);
    $underReview = DevelopmentTask::factory()->underReview()->create(['submitted_at' => now()->subMinute()]);
    DevelopmentTask::factory()->planned()->create();

    $this->actingAs($reviewer)
        ->get(route('development.review.index'))
        ->assertSuccessful()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->component('Development/Review/Index')
            ->has('requests.data', 3)
            ->where('requests.data.0.id', $submitted->uuid)
            ->where('requests.data.1.id', $needsInformation->uuid)
            ->where('requests.data.2.id', $underReview->uuid));
});

test('reviewers can request information and requesters can reply in the participant thread', function (): void {
    $requester = developmentWorkflowUser(
        DevelopmentPermission::DevelopmentRequestsCreate,
        'Clarification Requester',
    );
    $reviewer = developmentWorkflowUser(
        DevelopmentPermission::DevelopmentRequestsReview,
        'Clarification Reviewer',
    );
    $task = DevelopmentTask::factory()->for($requester, 'submitter')->create();
    Notification::fake();

    $this->actingAs($reviewer)
        ->post(route('development.tasks.informationRequest.store', $task), [
            'body' => 'Which roles need access to this report?',
        ])
        ->assertRedirect();

    $task->refresh();
    $requestNote = $task->notes()->sole();

    expect($task->status)->toBe(DevelopmentTaskStatus::NeedsInformation)
        ->and($task->reviewer?->is($reviewer))->toBeTrue()
        ->and($requestNote->audience)->toBe(DevelopmentNoteAudience::Participants)
        ->and($requestNote->body)->toBe('Which roles need access to this report?');

    Notification::assertSentTo(
        $requester,
        DevelopmentTaskInformationRequested::class,
        fn (DevelopmentTaskInformationRequested $notification): bool => $notification->taskUuid === $task->uuid,
    );

    $this->actingAs($requester)
        ->post(route('development.tasks.notes.store', $task), [
            'body' => 'Admins and Sales Ops need access.',
            'audience' => DevelopmentNoteAudience::Participants->value,
        ])
        ->assertRedirect();

    expect($task->notes()->count())->toBe(2);
    Notification::assertSentTo(
        $reviewer,
        DevelopmentTaskClarificationAdded::class,
        fn (DevelopmentTaskClarificationAdded $notification): bool => $notification->body === 'Admins and Sales Ops need access.',
    );
});

test('requesters cannot create or see internal planning notes', function (): void {
    $requester = developmentWorkflowUser(
        DevelopmentPermission::DevelopmentRequestsCreate,
        'Private Notes Requester',
    );
    $reviewer = developmentWorkflowUser(
        DevelopmentPermission::DevelopmentRequestsReview,
        'Private Notes Reviewer',
    );
    $task = DevelopmentTask::factory()->for($requester, 'submitter')->underReview()->create([
        'reviewer_id' => $reviewer->getKey(),
    ]);
    $participantNote = DevelopmentTaskNote::factory()->for($task, 'task')->create();
    $internalNote = DevelopmentTaskNote::factory()->for($task, 'task')->internal()->create();

    $this->actingAs($requester)
        ->post(route('development.tasks.notes.store', $task), [
            'body' => 'This should remain private.',
            'audience' => DevelopmentNoteAudience::DevelopmentTeam->value,
        ])
        ->assertForbidden();

    $this->actingAs($requester)
        ->get(route('development.tasks.show', $task))
        ->assertSuccessful()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->has('task.notes', 1)
            ->where('task.notes.0.id', $participantNote->uuid)
            ->missing('task.notes.1'));

    $this->actingAs($reviewer)
        ->get(route('development.tasks.show', $task))
        ->assertSuccessful()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->has('task.notes', 2)
            ->where('task.notes.1.id', $internalNote->uuid));
});

test('reviewers can start review and promote a request to the roadmap', function (): void {
    $requester = developmentWorkflowUser(
        DevelopmentPermission::DevelopmentRequestsCreate,
        'Promotion Requester',
    );
    $reviewer = developmentWorkflowUser(
        DevelopmentPermission::DevelopmentRequestsReview,
        'Promotion Reviewer',
    );
    DevelopmentTask::factory()->planned()->create(['roadmap_position' => 1]);
    DevelopmentTask::factory()->planned()->create(['roadmap_position' => 2]);
    $task = DevelopmentTask::factory()->for($requester, 'submitter')->create();
    Notification::fake();

    $this->actingAs($reviewer)
        ->post(route('development.tasks.review.store', $task))
        ->assertRedirect(route('development.tasks.show', $task));

    expect($task->refresh()->status)->toBe(DevelopmentTaskStatus::UnderReview)
        ->and($task->reviewer?->is($reviewer))->toBeTrue();

    $this->actingAs($reviewer)
        ->post(route('development.tasks.promote', $task))
        ->assertRedirect(route('development.tasks.show', $task));

    expect($task->refresh()->status)->toBe(DevelopmentTaskStatus::Planned)
        ->and($task->planned_at)->not->toBeNull()
        ->and($task->roadmap_position)->toBe(3);
    Notification::assertSentTo($requester, DevelopmentTaskPromoted::class);
});

test('rejection records a public reason, notifies the requester, and archives the request', function (): void {
    $requester = developmentWorkflowUser(
        DevelopmentPermission::DevelopmentRequestsCreate,
        'Rejection Requester',
    );
    $reviewer = developmentWorkflowUser(
        DevelopmentPermission::DevelopmentRequestsReview,
        'Rejection Reviewer',
    );
    $task = DevelopmentTask::factory()
        ->for($requester, 'submitter')
        ->underReview()
        ->create(['reviewer_id' => $reviewer->getKey()]);
    Notification::fake();

    $this->actingAs($reviewer)
        ->post(route('development.tasks.reject', $task), [
            'body' => 'The existing reporting screen already covers this outcome.',
        ])
        ->assertRedirect(route('development.review.index'));

    $archivedTask = DevelopmentTask::withTrashed()->findOrFail($task->getKey());
    $reason = $archivedTask->notes()->sole();

    expect($archivedTask->status)->toBe(DevelopmentTaskStatus::Rejected)
        ->and($archivedTask->trashed())->toBeTrue()
        ->and($reason->audience)->toBe(DevelopmentNoteAudience::Participants)
        ->and($reason->body)->toBe('The existing reporting screen already covers this outcome.');
    Notification::assertSentTo(
        $requester,
        DevelopmentTaskRejected::class,
        fn (DevelopmentTaskRejected $notification): bool => $notification->reason === $reason->body,
    );
});
