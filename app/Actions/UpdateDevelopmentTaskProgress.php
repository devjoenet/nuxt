<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\DevelopmentTaskProgressData;
use App\Data\DevelopmentTaskTransitionData;
use App\Enums\DevelopmentTaskStatus;
use App\Models\DevelopmentTask;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdateDevelopmentTaskProgress
{
    public function __construct(private TransitionDevelopmentTask $transitionDevelopmentTask) {}

    /** @param array<string, mixed> $attributes */
    public function handleRaw(DevelopmentTask $task, array $attributes, User $actor): DevelopmentTask
    {
        return $this->handle($task, DevelopmentTaskProgressData::fromRaw($attributes), $actor);
    }

    public function handle(
        DevelopmentTask $task,
        DevelopmentTaskProgressData $data,
        User $actor,
    ): DevelopmentTask {
        return DB::transaction(function () use ($task, $data, $actor): DevelopmentTask {
            $task = DevelopmentTask::query()
                ->whereKey($task->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($data->status === DevelopmentTaskStatus::InProgress && $task->assignee_id === null) {
                throw ValidationException::withMessages([
                    'assignee_id' => 'Assign this task before starting work.',
                ]);
            }

            return $this->transitionDevelopmentTask->handle(
                $task,
                new DevelopmentTaskTransitionData($data->status),
                $actor,
            );
        });
    }
}
