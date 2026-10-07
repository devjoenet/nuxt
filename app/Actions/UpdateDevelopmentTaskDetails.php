<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\DevelopmentTaskData;
use App\Models\DevelopmentTask;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Spatie\Activitylog\Facades\Activity;

class UpdateDevelopmentTaskDetails
{
    /** @param array<string, mixed> $attributes */
    public function handleRaw(DevelopmentTask $task, array $attributes, User $actor): DevelopmentTask
    {
        return $this->handle($task, DevelopmentTaskData::fromRaw($attributes), $actor);
    }

    public function handle(DevelopmentTask $task, DevelopmentTaskData $data, User $actor): DevelopmentTask
    {
        return DB::transaction(function () use ($task, $data, $actor): DevelopmentTask {
            $task = DevelopmentTask::query()
                ->withTrashed()
                ->whereKey($task->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($task->trashed() || $task->status->isTerminal()) {
                throw ValidationException::withMessages([
                    'status' => 'Only active development tasks can have their scope updated.',
                ]);
            }

            Activity::defaultCauser($actor, fn (): bool => $task->update([
                'title' => $data->title,
                'request_type' => $data->requestType,
                'affected_area' => $data->affectedArea,
                'description' => $data->description,
                'desired_outcome' => $data->desiredOutcome,
                'acceptance_criteria' => $data->acceptanceCriteria,
            ]));

            return $task->refresh();
        }, attempts: 5);
    }
}
