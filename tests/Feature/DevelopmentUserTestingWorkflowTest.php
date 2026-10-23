<?php

declare(strict_types=1);

use App\Enums\Permission as DevelopmentPermission;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Inertia\Testing\AssertableInertia;
use SoutheastCode\DevelopmentWorkflow\Enums\DevelopmentTaskStatus;
use SoutheastCode\DevelopmentWorkflow\Enums\DevelopmentTestRunStatus;
use SoutheastCode\DevelopmentWorkflow\Models\DevelopmentTask;
use SoutheastCode\DevelopmentWorkflow\Models\DevelopmentTestRun;

beforeEach(function (): void {
    app(RolePermissionSeeder::class)->run();
    $this->withoutVite();
});

test('roadmap managers request task-specific testing rounds', function (): void {
    $manager = User::factory()->create();
    $manager->givePermissionTo(DevelopmentPermission::DevelopmentRoadmapManage->value);
    $tester = User::factory()->create(['name' => 'Assigned Tester']);
    $task = DevelopmentTask::factory()->inProgress()->create([
        'title' => 'Verify development task rounds',
    ]);

    $this->actingAs($manager)
        ->post(route('development.tasks.testRuns.store', $task), [
            'development_task_id' => $task->uuid,
            'tester_id' => $tester->uuid,
            'instructions' => 'Verify the acceptance criteria on desktop and mobile.',
            'build_reference' => 'https://staging.example.com/build/42',
        ])
        ->assertRedirect()
        ->assertSessionHas('success', 'User testing requested.');

    $testRun = DevelopmentTestRun::query()->sole();

    expect($task->refresh()->status)->toBe(DevelopmentTaskStatus::ReadyForTesting)
        ->and($task->ready_for_testing_at)->not->toBeNull()
        ->and($testRun->task->is($task))->toBeTrue()
        ->and($testRun->tester?->is($tester))->toBeTrue()
        ->and($testRun->requestedBy?->is($manager))->toBeTrue()
        ->and($testRun->status)->toBe(DevelopmentTestRunStatus::Pending)
        ->and($testRun->instructions)->toBe('Verify the acceptance criteria on desktop and mobile.')
        ->and($testRun->build_reference)->toBe('https://staging.example.com/build/42');

    $this->actingAs($tester)
        ->get(route('development.userTesting.index'))
        ->assertSuccessful()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->component('dev::UserTesting/Index', false)
            ->has('testRuns', 1)
            ->where('testRuns.0.id', $testRun->uuid)
            ->where('testRuns.0.task.id', $task->uuid)
            ->where('testRuns.0.tester', 'Assigned Tester')
            ->where('testRuns.0.can.record_result', true)
            ->missing('pages'));
});

test('assigned testers see only their pending rounds without a management permission', function (): void {
    $tester = User::factory()->create();
    $otherTester = User::factory()->create();
    $ownRun = DevelopmentTestRun::factory()->for($tester, 'tester')->create();
    DevelopmentTestRun::factory()->for($otherTester, 'tester')->create();

    $this->actingAs($tester)
        ->get(route('development.userTesting.index'))
        ->assertSuccessful()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->has('testRuns', 1)
            ->where('testRuns.0.id', $ownRun->uuid)
            ->where('auth.user.has_pending_development_tests', true));

    $unassignedUser = User::factory()->create();

    $this->actingAs($unassignedUser)
        ->get(route('development.userTesting.index'))
        ->assertForbidden();
});

test('requesting changes preserves the round and returns the task to implementation', function (): void {
    $manager = User::factory()->create();
    $manager->givePermissionTo(DevelopmentPermission::DevelopmentRoadmapManage->value);
    $assignee = User::factory()->create();
    $tester = User::factory()->create();
    $task = DevelopmentTask::factory()
        ->inProgress()
        ->create(['assignee_id' => $assignee->getKey()]);

    $this->actingAs($manager)->post(route('development.tasks.testRuns.store', $task), [
        'development_task_id' => $task->uuid,
        'tester_id' => $tester->uuid,
        'instructions' => 'Check the responsive task controls.',
    ]);
    $testRun = DevelopmentTestRun::query()->sole();

    $this->actingAs($tester)
        ->patch(route('development.testRuns.update', $testRun), [
            'development_task_id' => $task->uuid,
            'status' => DevelopmentTestRunStatus::ChangesRequested->value,
        ])
        ->assertSessionHasErrors('feedback');

    $this->actingAs($tester)
        ->patch(route('development.testRuns.update', $testRun), [
            'development_task_id' => $task->uuid,
            'status' => DevelopmentTestRunStatus::ChangesRequested->value,
            'feedback' => 'The accept control overflows at the small breakpoint.',
        ])
        ->assertRedirect(route('development.tasks.show', $task));

    expect($testRun->refresh()->status)->toBe(DevelopmentTestRunStatus::ChangesRequested)
        ->and($testRun->feedback)->toBe('The accept control overflows at the small breakpoint.')
        ->and($testRun->completed_at)->not->toBeNull()
        ->and($task->refresh()->status)->toBe(DevelopmentTaskStatus::ChangesRequested)
        ->and($task->changes_requested_at)->not->toBeNull();

    $this->actingAs($assignee)
        ->patch(route('development.tasks.progress.update', $task), [
            'status' => DevelopmentTaskStatus::InProgress->value,
        ])
        ->assertRedirect();

    expect($task->refresh()->status)->toBe(DevelopmentTaskStatus::InProgress)
        ->and($task->testRuns()->count())->toBe(1);
});

test('accepted testing completes and soft-archives the task atomically', function (): void {
    $tester = User::factory()->create();
    $task = DevelopmentTask::factory()->readyForTesting()->create();
    $testRun = DevelopmentTestRun::factory()
        ->for($task, 'task')
        ->for($tester, 'tester')
        ->create();

    $this->actingAs($tester)
        ->patch(route('development.testRuns.update', $testRun), [
            'development_task_id' => $task->uuid,
            'status' => DevelopmentTestRunStatus::Accepted->value,
            'feedback' => 'Acceptance criteria pass on the requested build.',
        ])
        ->assertRedirect(route('development.userTesting.index'))
        ->assertSessionHas('success', 'Testing accepted. The task is complete and archived.');

    $archivedTask = DevelopmentTask::query()->withTrashed()->findOrFail($task->getKey());

    expect($testRun->refresh()->status)->toBe(DevelopmentTestRunStatus::Accepted)
        ->and($testRun->feedback)->toBe('Acceptance criteria pass on the requested build.')
        ->and($testRun->completed_at)->not->toBeNull()
        ->and($archivedTask->status)->toBe(DevelopmentTaskStatus::Completed)
        ->and($archivedTask->completed_at)->not->toBeNull()
        ->and($archivedTask->trashed())->toBeTrue();
});

test('testing routes reject duplicate rounds and unrelated result actors', function (): void {
    $manager = User::factory()->create();
    $manager->givePermissionTo(DevelopmentPermission::DevelopmentRoadmapManage->value);
    $tester = User::factory()->create();
    $unrelatedUser = User::factory()->create();
    $task = DevelopmentTask::factory()->inProgress()->create();

    $this->actingAs($manager)->post(route('development.tasks.testRuns.store', $task), [
        'development_task_id' => $task->uuid,
        'tester_id' => $tester->uuid,
        'instructions' => 'Verify the requested behavior.',
    ]);
    $testRun = DevelopmentTestRun::query()->sole();

    $this->actingAs($unrelatedUser)
        ->patch(route('development.testRuns.update', $testRun), [
            'development_task_id' => $task->uuid,
            'status' => DevelopmentTestRunStatus::Accepted->value,
        ])
        ->assertForbidden();

    $task->update(['status' => DevelopmentTaskStatus::InProgress]);

    $this->actingAs($manager)
        ->post(route('development.tasks.testRuns.store', $task), [
            'development_task_id' => $task->uuid,
            'tester_id' => $tester->uuid,
            'instructions' => 'Create an accidental duplicate.',
        ])
        ->assertSessionHasErrors('status');

    expect($task->testRuns()->count())->toBe(1)
        ->and($testRun->refresh()->status)->toBe(DevelopmentTestRunStatus::Pending);
});

test('stale testing forms cannot transition a different task', function (): void {
    $manager = User::factory()->create();
    $manager->givePermissionTo(DevelopmentPermission::DevelopmentRoadmapManage->value);
    $tester = User::factory()->create();
    $displayedTask = DevelopmentTask::factory()->inProgress()->create([
        'title' => "Group permission list on a role's page",
    ]);
    $routeTask = DevelopmentTask::factory()->inProgress()->create([
        'title' => 'Expand admin search results',
    ]);

    $this->actingAs($manager)
        ->post(route('development.tasks.testRuns.store', $routeTask), [
            'development_task_id' => $displayedTask->uuid,
            'tester_id' => $tester->uuid,
            'instructions' => 'Verify the task shown in the workspace.',
        ])
        ->assertSessionHasErrors('development_task_id');

    expect($displayedTask->refresh()->status)->toBe(DevelopmentTaskStatus::InProgress)
        ->and($routeTask->refresh()->status)->toBe(DevelopmentTaskStatus::InProgress)
        ->and(DevelopmentTestRun::query()->exists())->toBeFalse();

    $this->actingAs($manager)
        ->post(route('development.tasks.testRuns.store', $routeTask), [
            'development_task_id' => $routeTask->uuid,
            'tester_id' => $tester->uuid,
            'instructions' => 'Verify the correct route task.',
        ])
        ->assertRedirect();

    $testRun = DevelopmentTestRun::query()->sole();

    $this->actingAs($tester)
        ->patch(route('development.testRuns.update', $testRun), [
            'development_task_id' => $displayedTask->uuid,
            'status' => DevelopmentTestRunStatus::Accepted->value,
        ])
        ->assertSessionHasErrors('development_task_id');

    expect($displayedTask->refresh()->status)->toBe(DevelopmentTaskStatus::InProgress)
        ->and($routeTask->refresh()->status)->toBe(DevelopmentTaskStatus::ReadyForTesting)
        ->and($testRun->refresh()->status)->toBe(DevelopmentTestRunStatus::Pending)
        ->and($routeTask->trashed())->toBeFalse();
});
