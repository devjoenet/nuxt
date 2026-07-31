<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\DevelopmentTaskStatus;
use App\Models\DevelopmentTask;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Spatie\Activitylog\Facades\Activity;

class SetDevelopmentTaskRoadmapPosition
{
    public function handle(
        DevelopmentTask $task,
        ?int $position,
        User $actor,
    ): DevelopmentTask {
        if ($position !== null && $position < 1) {
            throw ValidationException::withMessages([
                'roadmap_position' => 'The planned queue position must be at least 1.',
            ]);
        }

        return DB::transaction(function () use ($task, $position, $actor): DevelopmentTask {
            $plannedTasks = DevelopmentTask::query()
                ->where('status', DevelopmentTaskStatus::Planned)
                ->orderByRaw('roadmap_position is null')
                ->orderBy('roadmap_position')
                ->oldest('planned_at')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            $lockedTask = $plannedTasks->firstWhere('id', $task->getKey())
                ?? DevelopmentTask::query()
                    ->whereKey($task->getKey())
                    ->lockForUpdate()
                    ->firstOrFail();

            if ($position !== null && $lockedTask->status !== DevelopmentTaskStatus::Planned) {
                throw ValidationException::withMessages([
                    'roadmap_position' => 'Only planned tasks have a queue position.',
                ]);
            }

            $orderedTasks = $plannedTasks
                ->reject(fn (DevelopmentTask $plannedTask): bool => $plannedTask->is($lockedTask))
                ->values();

            if ($position !== null) {
                $boundedPosition = min($position, $orderedTasks->count() + 1);
                $orderedTasks->splice($boundedPosition - 1, 0, [$lockedTask]);
            }

            Activity::defaultCauser($actor, function () use ($orderedTasks, $lockedTask, $position): void {
                $orderedTasks->each(function (DevelopmentTask $plannedTask, int $index): void {
                    $queuePosition = $index + 1;

                    if ($plannedTask->roadmap_position !== $queuePosition) {
                        $plannedTask->update(['roadmap_position' => $queuePosition]);
                    }
                });

                if ($position === null && $lockedTask->roadmap_position !== null) {
                    $lockedTask->update(['roadmap_position' => null]);
                }
            });

            return $lockedTask->refresh();
        }, attempts: 5);
    }
}
