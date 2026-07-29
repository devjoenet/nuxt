<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\DevelopmentTaskTransitionData;
use App\Data\DevelopmentTestResultData;
use App\Enums\DevelopmentTaskStatus;
use App\Enums\DevelopmentTestRunStatus;
use App\Models\DevelopmentTask;
use App\Models\DevelopmentTestRun;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RecordDevelopmentTestResult
{
    public function __construct(private TransitionDevelopmentTask $transitionDevelopmentTask) {}

    /** @param array<string, mixed> $attributes */
    public function handleRaw(DevelopmentTestRun $testRun, array $attributes, User $actor): DevelopmentTestRun
    {
        return $this->handle($testRun, DevelopmentTestResultData::fromRaw($attributes), $actor);
    }

    public function handle(
        DevelopmentTestRun $testRun,
        DevelopmentTestResultData $data,
        User $actor,
    ): DevelopmentTestRun {
        return DB::transaction(function () use ($testRun, $data, $actor): DevelopmentTestRun {
            $taskId = DevelopmentTestRun::query()
                ->whereKey($testRun->getKey())
                ->valueOrFail('development_task_id');

            $task = DevelopmentTask::query()
                ->withTrashed()
                ->whereKey($taskId)
                ->lockForUpdate()
                ->firstOrFail();
            $testRun = DevelopmentTestRun::query()
                ->whereKey($testRun->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($testRun->status !== DevelopmentTestRunStatus::Pending) {
                throw ValidationException::withMessages([
                    'status' => 'This test round already has a result.',
                ]);
            }

            if ($task->trashed() || $task->status !== DevelopmentTaskStatus::ReadyForTesting) {
                throw ValidationException::withMessages([
                    'status' => 'This task is no longer awaiting a test result.',
                ]);
            }

            $testRun->update([
                'status' => $data->status,
                'feedback' => $data->feedback,
                'completed_at' => now(),
            ]);

            $taskStatus = $data->status === DevelopmentTestRunStatus::Accepted
                ? DevelopmentTaskStatus::Completed
                : DevelopmentTaskStatus::ChangesRequested;

            $task = $this->transitionDevelopmentTask->handle(
                $task,
                new DevelopmentTaskTransitionData($taskStatus),
                $actor,
            );

            $testRun = $testRun->refresh()->load(['tester', 'requestedBy']);
            $testRun->setRelation('task', $task);

            return $testRun;
        });
    }
}
