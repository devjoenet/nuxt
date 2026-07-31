<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\UpdateDevelopmentTaskRoadmapData;
use App\Enums\DevelopmentTaskStatus;
use App\Models\DevelopmentTask;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Spatie\Activitylog\Facades\Activity;

class UpdateDevelopmentTaskRoadmap
{
    public function __construct(private SetDevelopmentTaskRoadmapPosition $setDevelopmentTaskRoadmapPosition) {}

    /** @param array<string, mixed> $attributes */
    public function handleRaw(DevelopmentTask $task, array $attributes, User $actor): DevelopmentTask
    {
        return $this->handle($task, UpdateDevelopmentTaskRoadmapData::fromRaw($attributes), $actor);
    }

    public function handle(
        DevelopmentTask $task,
        UpdateDevelopmentTaskRoadmapData $data,
        User $actor,
    ): DevelopmentTask {
        return DB::transaction(function () use ($task, $data, $actor): DevelopmentTask {
            if ($task->status === DevelopmentTaskStatus::Planned && $data->roadmapPosition === null) {
                throw ValidationException::withMessages([
                    'roadmap_position' => 'Planned tasks require a queue position.',
                ]);
            }

            $task = $task->status === DevelopmentTaskStatus::Planned
                ? $this->setDevelopmentTaskRoadmapPosition->handle(
                    $task,
                    $data->roadmapPosition,
                    $actor,
                )
                : DevelopmentTask::query()
                    ->whereKey($task->getKey())
                    ->lockForUpdate()
                    ->firstOrFail();

            if (! in_array($task->status, [
                DevelopmentTaskStatus::Planned,
                DevelopmentTaskStatus::InProgress,
                DevelopmentTaskStatus::Blocked,
                DevelopmentTaskStatus::ChangesRequested,
            ], true)) {
                throw ValidationException::withMessages([
                    'status' => 'Only active roadmap tasks can be prioritized or assigned.',
                ]);
            }

            if ($task->status !== DevelopmentTaskStatus::Planned && $data->roadmapPosition !== null) {
                throw ValidationException::withMessages([
                    'roadmap_position' => 'Only planned tasks have a queue position.',
                ]);
            }

            $assignee = $data->assigneeId === null
                ? null
                : User::query()
                    ->where('uuid', $data->assigneeId)
                    ->where('is_active', true)
                    ->firstOrFail();

            Activity::defaultCauser($actor, function () use ($task, $data, $assignee): void {
                $task->update([
                    'priority' => $data->priority,
                    'roadmap_position' => $task->status === DevelopmentTaskStatus::Planned
                        ? $task->roadmap_position
                        : null,
                    'assignee_id' => $assignee?->getKey(),
                ]);
            });

            return $task->refresh()->load(['submitter', 'reviewer', 'assignee']);
        }, attempts: 5);
    }
}
