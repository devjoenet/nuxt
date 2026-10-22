<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use SoutheastCode\Crm\FollowUps\Enums\FollowUpTaskStatus;
use SoutheastCode\Crm\FollowUps\Models\FollowUpTask;
use SoutheastCode\Crm\Leads\Models\Lead;
use SoutheastCode\Crm\Shared\Enums\CrmActivityType;
use Spatie\Activitylog\Facades\Activity;

class CompleteFollowUpTask
{
    public function handle(Lead $lead, FollowUpTask $task, User $actor): FollowUpTask
    {
        return DB::transaction(function () use ($lead, $task, $actor): FollowUpTask {
            $task = FollowUpTask::query()->lockForUpdate()->findOrFail($task->id);

            if ($task->lead_id !== $lead->id) {
                throw (new ModelNotFoundException())->setModel(FollowUpTask::class);
            }

            if ($task->status === FollowUpTaskStatus::Completed) {
                return $task;
            }

            if ($task->status !== FollowUpTaskStatus::Open) {
                throw ValidationException::withMessages(['status' => 'Only open follow-ups can be completed.']);
            }

            Activity::defaultCauser($actor, function () use ($lead, $task, $actor): void {
                $task->update([
                    'status' => FollowUpTaskStatus::Completed,
                    'completed_at' => now(),
                ]);

                $lead->activities()->create([
                    'user_id' => $actor->id,
                    'type' => CrmActivityType::TaskCompleted,
                    'description' => "Follow-up completed: {$task->title}",
                ]);
            });

            return $task->refresh();
        });
    }
}
