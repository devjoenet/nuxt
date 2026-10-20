<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\DevelopmentTaskTransitionData;
use App\Data\RequestDevelopmentTaskTestingData;
use App\Enums\DevelopmentTaskStatus;
use App\Enums\DevelopmentTestRunStatus;
use App\Models\DevelopmentTask;
use App\Models\DevelopmentTestRun;
use App\Models\User;
use App\Support\WorkspaceUsers;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RequestDevelopmentTaskTesting
{
    public function __construct(private TransitionDevelopmentTask $transitionDevelopmentTask) {}

    /** @param array<string, mixed> $attributes */
    public function handleRaw(DevelopmentTask $task, array $attributes, User $actor): DevelopmentTestRun
    {
        return $this->handle($task, RequestDevelopmentTaskTestingData::fromRaw($attributes), $actor);
    }

    public function handle(
        DevelopmentTask $task,
        RequestDevelopmentTaskTestingData $data,
        User $actor,
    ): DevelopmentTestRun {
        return DB::transaction(function () use ($task, $data, $actor): DevelopmentTestRun {
            $task = DevelopmentTask::query()
                ->whereKey($task->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($task->status !== DevelopmentTaskStatus::InProgress) {
                throw ValidationException::withMessages([
                    'status' => 'Only in-progress tasks can be sent to user testing.',
                ]);
            }

            if ($task->testRuns()->where('status', DevelopmentTestRunStatus::Pending)->exists()) {
                throw ValidationException::withMessages([
                    'status' => 'This task already has a pending test round.',
                ]);
            }

            $tester = WorkspaceUsers::model()::query()
                ->where('uuid', $data->testerId)
                ->where('is_active', true)
                ->firstOrFail();

            $testRun = $task->testRuns()->create([
                'tester_id' => $tester->getKey(),
                'requested_by_id' => $actor->getKey(),
                'instructions' => $data->instructions,
                'build_reference' => $data->buildReference,
                'status' => DevelopmentTestRunStatus::Pending,
                'requested_at' => now(),
            ]);

            $this->transitionDevelopmentTask->handle(
                $task,
                new DevelopmentTaskTransitionData(DevelopmentTaskStatus::ReadyForTesting),
                $actor,
            );

            return $testRun->refresh()->load(['task', 'tester', 'requestedBy']);
        });
    }
}
