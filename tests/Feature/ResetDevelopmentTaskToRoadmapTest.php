<?php

declare(strict_types=1);

use App\Enums\Permission as DevelopmentPermission;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;
use SoutheastCode\DevelopmentWorkflow\Actions\ResetDevelopmentTaskToRoadmap;
use SoutheastCode\DevelopmentWorkflow\Enums\DevelopmentTaskStatus;
use SoutheastCode\DevelopmentWorkflow\Models\DevelopmentTask;
use SoutheastCode\DevelopmentWorkflow\Models\DevelopmentTestRun;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Permission;

function roadmapResetManager(): User
{
    $user = User::factory()->create();
    $user->givePermissionTo(Permission::findOrCreate(DevelopmentPermission::DevelopmentRoadmapManage->value, 'web'));

    return $user;
}

test('an accidental start can be reset without losing review history or queue order', function (): void {
    $manager = roadmapResetManager();
    $queued = DevelopmentTask::factory()->planned()->create(['roadmap_position' => 1]);
    $task = DevelopmentTask::factory()->inProgress()->create(['blocked_at' => now()->subHour()]);
    $plannedAt = $task->planned_at;
    $reviewerId = $task->reviewer_id;

    $reset = app(ResetDevelopmentTaskToRoadmap::class)->handle($task, 1, $manager);

    expect($reset->status)->toBe(DevelopmentTaskStatus::Planned)
        ->and($reset->roadmap_position)->toBe(1)
        ->and($queued->refresh()->roadmap_position)->toBe(2)
        ->and($reset->assignee_id)->toBeNull()
        ->and($reset->started_at)->toBeNull()
        ->and($reset->blocked_at)->toBeNull()
        ->and($reset->planned_at)->toEqual($plannedAt)
        ->and($reset->reviewer_id)->toBe($reviewerId);

    $activity = Activity::query()->forSubject($task)->latest('id')->firstOrFail();
    expect($activity->causer?->is($manager))->toBeTrue();
});

test('a roadmap reset requires roadmap management permission', function (): void {
    $task = DevelopmentTask::factory()->inProgress()->create();

    expect(fn () => app(ResetDevelopmentTaskToRoadmap::class)->handle($task, 1, User::factory()->create()))
        ->toThrow(AuthorizationException::class);
    expect($task->refresh()->status)->toBe(DevelopmentTaskStatus::InProgress)
        ->and($task->started_at)->not->toBeNull();
});

test('a roadmap reset rejects tasks outside implementation and preserves the queue', function (): void {
    $queued = DevelopmentTask::factory()->planned()->create(['roadmap_position' => 3]);
    $task = DevelopmentTask::factory()->create();

    expect(fn () => app(ResetDevelopmentTaskToRoadmap::class)->handle($task, 1, roadmapResetManager()))
        ->toThrow(ValidationException::class);
    expect($task->refresh()->status)->toBe(DevelopmentTaskStatus::Submitted)
        ->and($queued->refresh()->roadmap_position)->toBe(3);
});

test('a roadmap reset preserves testing history by rejecting previously tested work', function (): void {
    $task = DevelopmentTask::factory()->inProgress()->create();
    $round = DevelopmentTestRun::factory()->changesRequested()->create(['development_task_id' => $task->id]);

    expect(fn () => app(ResetDevelopmentTaskToRoadmap::class)->handle($task, 1, roadmapResetManager()))
        ->toThrow(ValidationException::class);
    expect($task->refresh()->status)->toBe(DevelopmentTaskStatus::InProgress)
        ->and($task->testRuns()->sole()->is($round))->toBeTrue()
        ->and($task->started_at)->not->toBeNull();
});

test('a roadmap reset rejects invalid queue positions without changing the task', function (): void {
    $task = DevelopmentTask::factory()->inProgress()->create();

    expect(fn () => app(ResetDevelopmentTaskToRoadmap::class)->handle($task, 0, roadmapResetManager()))
        ->toThrow(ValidationException::class);
    expect($task->refresh()->status)->toBe(DevelopmentTaskStatus::InProgress);
});
