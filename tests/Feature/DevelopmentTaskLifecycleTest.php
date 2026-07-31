<?php

declare(strict_types=1);

use App\Actions\ArchiveDevelopmentTask;
use App\Actions\SubmitDevelopmentTask;
use App\Actions\TransitionDevelopmentTask;
use App\Data\DevelopmentTaskTransitionData;
use App\Enums\DevelopmentNoteAudience;
use App\Enums\DevelopmentRequestType;
use App\Enums\DevelopmentTaskArea;
use App\Enums\DevelopmentTaskLinkType;
use App\Enums\DevelopmentTaskStatus;
use App\Enums\DevelopmentTestRunStatus;
use App\Enums\TaskPriority;
use App\Models\DevelopmentTask;
use App\Models\DevelopmentTaskLink;
use App\Models\DevelopmentTaskNote;
use App\Models\DevelopmentTestRun;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use Spatie\Activitylog\Models\Activity;

$transitionCases = [];

foreach (DevelopmentTaskStatus::cases() as $source) {
    foreach (DevelopmentTaskStatus::cases() as $target) {
        $outcome = $source === $target
            ? 'idempotent'
            : ($source->canTransitionTo($target) ? 'allowed' : 'forbidden');

        $transitionCases["{$source->value} to {$target->value}"] = [$source, $target, $outcome];
    }
}

dataset('development task status transitions', $transitionCases);

test('a development request is submitted through a typed actor-aware action', function (): void {
    $actor = User::factory()->create();

    $task = app(SubmitDevelopmentTask::class)->handleRaw([
        'title' => '  Add project health reporting  ',
        'request_type' => DevelopmentRequestType::Feature->value,
        'affected_area' => DevelopmentTaskArea::Development->value,
        'description' => '  Project status is spread across several records.  ',
        'desired_outcome' => '  Give the team one current health view.  ',
        'acceptance_criteria' => '  Health is visible and permission scoped.  ',
    ], $actor);

    expect($task->title)->toBe('Add project health reporting')
        ->and($task->request_type)->toBe(DevelopmentRequestType::Feature)
        ->and($task->affected_area)->toBe(DevelopmentTaskArea::Development)
        ->and($task->description)->toBe('Project status is spread across several records.')
        ->and($task->desired_outcome)->toBe('Give the team one current health view.')
        ->and($task->acceptance_criteria)->toBe('Health is visible and permission scoped.')
        ->and($task->status)->toBe(DevelopmentTaskStatus::Submitted)
        ->and($task->priority)->toBe(TaskPriority::Normal)
        ->and($task->submitter->is($actor))->toBeTrue()
        ->and($task->submitted_at)->toEqual(now()->startOfSecond())
        ->and($task->trashed())->toBeFalse();

    $activity = Activity::query()
        ->where('subject_type', DevelopmentTask::class)
        ->where('subject_id', $task->getKey())
        ->latest('id')
        ->firstOrFail();

    expect($activity->log_name)->toBe('development')
        ->and($activity->event)->toBe('created')
        ->and($activity->causer?->is($actor))->toBeTrue()
        ->and($activity->attribute_changes?->toArray()['attributes'] ?? [])
        ->not->toHaveKeys(['description', 'desired_outcome', 'acceptance_criteria']);
});

test('raw submission validates before the action mutates state', function (): void {
    $actor = User::factory()->create();

    expect(fn (): DevelopmentTask => app(SubmitDevelopmentTask::class)->handleRaw([
        'title' => '   ',
        'request_type' => 'guess',
        'affected_area' => 'somewhere',
    ], $actor))->toThrow(ValidationException::class);

    expect(DevelopmentTask::query()->exists())->toBeFalse();
});

test('raw lifecycle transitions validate before using the shared action', function (): void {
    $actor = User::factory()->create();
    $task = DevelopmentTask::factory()->create();
    $action = app(TransitionDevelopmentTask::class);

    $transitioned = $action->handleRaw($task, [
        'status' => DevelopmentTaskStatus::UnderReview->value,
    ], $actor);

    expect($transitioned->status)->toBe(DevelopmentTaskStatus::UnderReview);

    expect(fn (): DevelopmentTask => $action->handleRaw($transitioned, [
        'status' => 'invented_status',
    ], $actor))->toThrow(ValidationException::class);

    expect($transitioned->refresh()->status)->toBe(DevelopmentTaskStatus::UnderReview);
});

test('deleting a submitter preserves the development record', function (): void {
    $submitter = User::factory()->create();
    $task = DevelopmentTask::factory()->for($submitter, 'submitter')->create();

    $submitter->delete();
    $task->refresh();

    expect($task->submitter_id)->toBeNull()
        ->and($task->submitter)->toBeNull()
        ->and($task->exists)->toBeTrue();
});

test('the transition action enforces the complete lifecycle matrix', function (
    DevelopmentTaskStatus $source,
    DevelopmentTaskStatus $target,
    string $outcome,
): void {
    $actor = User::factory()->create();
    $task = DevelopmentTask::factory()->create([
        'status' => $source,
        'assignee_id' => $target === DevelopmentTaskStatus::InProgress
            ? User::factory()
            : null,
        $source->timestampColumn() => now()->subDay(),
        'deleted_at' => $source->isTerminal() ? now()->subDay() : null,
    ]);
    $action = app(TransitionDevelopmentTask::class);

    if ($outcome === 'forbidden') {
        expect(fn (): DevelopmentTask => $action->handle(
            $task,
            new DevelopmentTaskTransitionData($target),
            $actor,
        ))->toThrow(ValidationException::class);

        $task->refresh();

        expect($task->status)->toBe($source)
            ->and($task->trashed())->toBe($source->isTerminal());

        return;
    }

    $transitioned = $action->handle(
        $task,
        new DevelopmentTaskTransitionData($target),
        $actor,
    );

    expect($transitioned->status)->toBe($target);

    if ($outcome === 'idempotent') {
        expect($transitioned->trashed())->toBe($source->isTerminal());

        return;
    }

    expect($transitioned->getAttribute($target->timestampColumn()))
        ->toEqual(now()->startOfSecond());

    $target->isTerminal()
        ? $this->assertSoftDeleted($transitioned)
        : $this->assertNotSoftDeleted($transitioned);
})->with('development task status transitions');

test('a stale task cannot bypass the latest lifecycle state', function (): void {
    $actor = User::factory()->create();
    $task = DevelopmentTask::factory()->create();
    $staleTask = $task->fresh();
    $action = app(TransitionDevelopmentTask::class);

    $action->handle(
        $task,
        new DevelopmentTaskTransitionData(DevelopmentTaskStatus::UnderReview),
        $actor,
    );

    expect(fn (): DevelopmentTask => $action->handle(
        $staleTask,
        new DevelopmentTaskTransitionData(DevelopmentTaskStatus::NeedsInformation),
        $actor,
    ))->toThrow(ValidationException::class);

    expect($task->refresh()->status)->toBe(DevelopmentTaskStatus::UnderReview);
});

test('transition activity records the explicit actor and bounded changes', function (): void {
    $actor = User::factory()->create();
    $task = DevelopmentTask::factory()->underReview()->create();

    app(TransitionDevelopmentTask::class)->handle(
        $task,
        new DevelopmentTaskTransitionData(DevelopmentTaskStatus::Planned),
        $actor,
    );

    $activity = Activity::query()
        ->where('subject_type', DevelopmentTask::class)
        ->where('subject_id', $task->getKey())
        ->latest('id')
        ->firstOrFail();
    $changes = $activity->attribute_changes?->toArray() ?? [];

    expect($activity->event)->toBe('updated')
        ->and($activity->causer?->is($actor))->toBeTrue()
        ->and($changes['old']['status'] ?? null)->toBe(DevelopmentTaskStatus::UnderReview->value)
        ->and($changes['attributes']['status'] ?? null)->toBe(DevelopmentTaskStatus::Planned->value)
        ->and($changes['attributes'] ?? [])->not->toHaveKeys([
            'description',
            'desired_outcome',
            'acceptance_criteria',
        ]);
});

test('archive refuses active tasks and is idempotent for terminal tasks', function (): void {
    $actor = User::factory()->create();
    $activeTask = DevelopmentTask::factory()->create();
    $completedTask = DevelopmentTask::factory()->create([
        'status' => DevelopmentTaskStatus::Completed,
        'completed_at' => now(),
        'deleted_at' => null,
    ]);
    $action = app(ArchiveDevelopmentTask::class);

    expect(fn (): DevelopmentTask => $action->handle($activeTask, $actor))
        ->toThrow(ValidationException::class);

    $archived = $action->handle($completedTask, $actor);
    $again = $action->handle($completedTask, $actor);

    $this->assertNotSoftDeleted($activeTask);
    $this->assertSoftDeleted($archived);

    expect($again->status)->toBe(DevelopmentTaskStatus::Completed)
        ->and($again->deleted_at)->toEqual($archived->deleted_at);
});

test('development task child records retain typed relationships and route keys', function (): void {
    $task = DevelopmentTask::factory()->readyForTesting()->create();
    $note = DevelopmentTaskNote::factory()->for($task, 'task')->internal()->create();
    $link = DevelopmentTaskLink::factory()->for($task, 'task')->create([
        'type' => DevelopmentTaskLinkType::Branch,
    ]);
    $testRun = DevelopmentTestRun::factory()->for($task, 'task')->create();

    expect($task->notes()->sole()->is($note))->toBeTrue()
        ->and($task->links()->sole()->is($link))->toBeTrue()
        ->and($task->testRuns()->sole()->is($testRun))->toBeTrue()
        ->and($note->audience)->toBe(DevelopmentNoteAudience::DevelopmentTeam)
        ->and($link->type)->toBe(DevelopmentTaskLinkType::Branch)
        ->and($testRun->status)->toBe(DevelopmentTestRunStatus::Pending)
        ->and($task->getRouteKey())->toBe($task->uuid)
        ->and($note->getRouteKey())->toBe($note->uuid)
        ->and($link->getRouteKey())->toBe($link->uuid)
        ->and($testRun->getRouteKey())->toBe($testRun->uuid);
});
