<?php

declare(strict_types=1);

use App\Enums\Permission as DevelopmentPermission;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use SoutheastCode\DevelopmentWorkflow\Enums\DevelopmentNoteAudience;
use SoutheastCode\DevelopmentWorkflow\Enums\DevelopmentTaskStatus;
use SoutheastCode\DevelopmentWorkflow\Models\DevelopmentTask;
use SoutheastCode\DevelopmentWorkflow\Models\DevelopmentTaskLink;
use SoutheastCode\DevelopmentWorkflow\Models\DevelopmentTaskNote;
use SoutheastCode\DevelopmentWorkflow\Models\DevelopmentTestRun;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

function userWithDevelopmentPermission(
    DevelopmentPermission $permission,
    string $roleName,
): User {
    $role = Role::findOrCreate($roleName, 'web');
    $role->givePermissionTo(Permission::findOrCreate($permission->value, 'web'));
    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

test('requesters can create and see only their own requests', function (): void {
    $requester = userWithDevelopmentPermission(
        DevelopmentPermission::DevelopmentRequestsCreate,
        'Development Requester',
    );
    $ownTask = DevelopmentTask::factory()->for($requester, 'submitter')->create();
    $otherTask = DevelopmentTask::factory()->create();

    expect(Gate::forUser($requester)->allows('viewAny', DevelopmentTask::class))->toBeTrue()
        ->and(Gate::forUser($requester)->allows('create', DevelopmentTask::class))->toBeTrue()
        ->and(Gate::forUser($requester)->allows('view', $ownTask))->toBeTrue()
        ->and(Gate::forUser($requester)->allows('view', $otherTask))->toBeFalse()
        ->and(Gate::forUser($requester)->allows('review', $ownTask))->toBeFalse()
        ->and(Gate::forUser($requester)->allows('manageRoadmap', $ownTask))->toBeFalse()
        ->and(Gate::forUser($requester)->allows('manageTesting', $ownTask))->toBeFalse()
        ->and(Gate::forUser($requester)->allows('cancel', $ownTask))->toBeTrue()
        ->and(Gate::forUser($requester)->allows('cancel', $otherTask))->toBeFalse();
});

test('reviewers and roadmap managers can see and manage all tasks in their boundary', function (): void {
    $reviewer = userWithDevelopmentPermission(
        DevelopmentPermission::DevelopmentRequestsReview,
        'Development Reviewer',
    );
    $roadmapManager = userWithDevelopmentPermission(
        DevelopmentPermission::DevelopmentRoadmapManage,
        'Development Roadmap Manager',
    );
    $task = DevelopmentTask::factory()->create();
    $plannedTask = DevelopmentTask::factory()->planned()->create();
    $completedTask = DevelopmentTask::factory()->completed()->create();
    $internalNote = DevelopmentTaskNote::factory()->for($task, 'task')->internal()->create();

    expect(Gate::forUser($reviewer)->allows('view', $task))->toBeTrue()
        ->and(Gate::forUser($reviewer)->allows('review', $task))->toBeTrue()
        ->and(Gate::forUser($reviewer)->allows('manageRoadmap', $task))->toBeFalse()
        ->and(Gate::forUser($reviewer)->allows('view', $internalNote))->toBeTrue()
        ->and(Gate::forUser($reviewer)->allows('cancel', $plannedTask))->toBeTrue()
        ->and(Gate::forUser($reviewer)->allows('archive', $completedTask))->toBeTrue()
        ->and(Gate::forUser($roadmapManager)->allows('view', $task))->toBeTrue()
        ->and(Gate::forUser($roadmapManager)->allows('review', $task))->toBeFalse()
        ->and(Gate::forUser($roadmapManager)->allows('manageRoadmap', $task))->toBeTrue()
        ->and(Gate::forUser($roadmapManager)->allows('archive', $task))->toBeFalse()
        ->and(Gate::forUser($roadmapManager)->allows('create', [
            DevelopmentTaskLink::class,
            $task,
        ]))->toBeTrue()
        ->and(Gate::forUser($roadmapManager)->allows('create', [
            DevelopmentTestRun::class,
            $task,
        ]))->toBeTrue();
});

test('request-only users cannot see internal planning notes', function (): void {
    $requester = userWithDevelopmentPermission(
        DevelopmentPermission::DevelopmentRequestsCreate,
        'Development Requester',
    );
    $task = DevelopmentTask::factory()->for($requester, 'submitter')->create();
    $participantNote = DevelopmentTaskNote::factory()->for($task, 'task')->create();
    $internalNote = DevelopmentTaskNote::factory()->for($task, 'task')->internal()->create();
    $link = DevelopmentTaskLink::factory()->for($task, 'task')->create();

    expect(Gate::forUser($requester)->allows('view', $participantNote))->toBeTrue()
        ->and(Gate::forUser($requester)->allows('view', $internalNote))->toBeFalse()
        ->and(Gate::forUser($requester)->allows('create', [
            DevelopmentTaskNote::class,
            $task,
            DevelopmentNoteAudience::Participants,
        ]))->toBeTrue()
        ->and(Gate::forUser($requester)->allows('create', [
            DevelopmentTaskNote::class,
            $task,
            DevelopmentNoteAudience::DevelopmentTeam,
        ]))->toBeFalse()
        ->and(Gate::forUser($requester)->allows('view', $link))->toBeTrue()
        ->and(Gate::forUser($requester)->allows('create', [
            DevelopmentTaskLink::class,
            $task,
        ]))->toBeFalse();
});

test('assignees can see internal planning and attach repository links', function (): void {
    $assignee = User::factory()->create();
    $task = DevelopmentTask::factory()->for($assignee, 'assignee')->create();
    $internalNote = DevelopmentTaskNote::factory()->for($task, 'task')->internal()->create();

    expect(Gate::forUser($assignee)->allows('view', $task))->toBeTrue()
        ->and(Gate::forUser($assignee)->allows('view', $internalNote))->toBeTrue()
        ->and(Gate::forUser($assignee)->allows('create', [
            DevelopmentTaskLink::class,
            $task,
        ]))->toBeTrue();
});

test('assigned testers can see and record only their testing work', function (): void {
    $tester = User::factory()->create();
    $otherTester = User::factory()->create();
    $task = DevelopmentTask::factory()->readyForTesting()->create();
    $testRun = DevelopmentTestRun::factory()
        ->for($task, 'task')
        ->for($tester, 'tester')
        ->create();
    $internalNote = DevelopmentTaskNote::factory()->for($task, 'task')->internal()->create();

    expect(Gate::forUser($tester)->allows('viewAny', DevelopmentTestRun::class))->toBeTrue()
        ->and(Gate::forUser($tester)->allows('view', $task))->toBeTrue()
        ->and(Gate::forUser($tester)->allows('view', $testRun))->toBeTrue()
        ->and(Gate::forUser($tester)->allows('update', $testRun))->toBeTrue()
        ->and(Gate::forUser($tester)->allows('view', $internalNote))->toBeFalse()
        ->and(Gate::forUser($otherTester)->allows('view', $task))->toBeFalse()
        ->and(Gate::forUser($otherTester)->allows('view', $testRun))->toBeFalse()
        ->and(Gate::forUser($otherTester)->allows('update', $testRun))->toBeFalse();
});

test('testing managers can see every task and test run', function (): void {
    $testingManager = userWithDevelopmentPermission(
        DevelopmentPermission::DevelopmentUserTestingManage,
        'Development Testing Manager',
    );
    $task = DevelopmentTask::factory()->create([
        'status' => DevelopmentTaskStatus::ReadyForTesting,
        'ready_for_testing_at' => now(),
    ]);
    $testRun = DevelopmentTestRun::factory()->for($task, 'task')->create();

    expect(Gate::forUser($testingManager)->allows('view', $task))->toBeTrue()
        ->and(Gate::forUser($testingManager)->allows('manageTesting', $task))->toBeTrue()
        ->and(Gate::forUser($testingManager)->allows('viewAny', DevelopmentTestRun::class))->toBeTrue()
        ->and(Gate::forUser($testingManager)->allows('view', $testRun))->toBeTrue()
        ->and(Gate::forUser($testingManager)->allows('update', $testRun))->toBeTrue();
});
