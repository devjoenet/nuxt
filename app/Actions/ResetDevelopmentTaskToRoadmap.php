<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\DevelopmentTaskStatus;
use App\Models\DevelopmentTask;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Spatie\Activitylog\Facades\Activity;

class ResetDevelopmentTaskToRoadmap
{
    public function __construct(private SetDevelopmentTaskRoadmapPosition $setDevelopmentTaskRoadmapPosition) {}

    public function handle(DevelopmentTask $task, int $position, User $actor): DevelopmentTask
    {
        Gate::forUser($actor)->authorize('manageRoadmap', $task);

        if ($position < 1) {
            throw ValidationException::withMessages([
                'roadmap_position' => 'The planned queue position must be at least 1.',
            ]);
        }

        return DB::transaction(function () use ($task, $position, $actor): DevelopmentTask {
            $task = $this->setDevelopmentTaskRoadmapPosition->handle($task, null, $actor);

            if ($task->status !== DevelopmentTaskStatus::InProgress || $task->testRuns()->exists()) {
                throw ValidationException::withMessages([
                    'status' => 'Only in-progress tasks without testing history can be reset to the roadmap.',
                ]);
            }

            Activity::defaultCauser($actor, function () use ($task): void {
                $task->update([
                    'status' => DevelopmentTaskStatus::Planned,
                    'assignee_id' => null,
                    'started_at' => null,
                    'blocked_at' => null,
                ]);
            });

            return $this->setDevelopmentTaskRoadmapPosition->handle($task, $position, $actor);
        }, attempts: 5);
    }
}
