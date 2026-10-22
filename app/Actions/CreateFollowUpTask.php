<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\FollowUpTaskData;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use LogicException;
use SoutheastCode\Crm\FollowUps\Models\FollowUpTask;
use SoutheastCode\Crm\Leads\Models\Lead;
use Spatie\Activitylog\Facades\Activity;

class CreateFollowUpTask
{
    public function handle(Lead $lead, FollowUpTaskData $data, User $actor): FollowUpTask
    {
        return DB::transaction(function () use ($lead, $data, $actor): FollowUpTask {
            $task = Activity::defaultCauser($actor, fn (): FollowUpTask => $lead->followUpTasks()->create([
                'title' => $data->title,
                'description' => $data->description,
                'priority' => $data->priority->value,
                'due_at' => $data->dueAt,
                'assigned_to' => $lead->owner_id,
            ]));

            return $task instanceof FollowUpTask ? $task : throw new LogicException('Follow-up task could not be created.');
        });
    }
}
