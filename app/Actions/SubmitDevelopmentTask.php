<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\DevelopmentTaskData;
use App\Enums\DevelopmentTaskStatus;
use App\Models\DevelopmentTask;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use LogicException;
use Spatie\Activitylog\Facades\Activity;

class SubmitDevelopmentTask
{
    /** @param array<string, mixed> $attributes */
    public function handleRaw(array $attributes, User $actor): DevelopmentTask
    {
        return $this->handle(DevelopmentTaskData::fromRaw($attributes), $actor);
    }

    public function handle(DevelopmentTaskData $data, User $actor): DevelopmentTask
    {
        return DB::transaction(function () use ($data, $actor): DevelopmentTask {
            $task = Activity::defaultCauser($actor, fn (): DevelopmentTask => DevelopmentTask::query()->create([
                'title' => $data->title,
                'request_type' => $data->requestType,
                'affected_area' => $data->affectedArea,
                'description' => $data->description,
                'desired_outcome' => $data->desiredOutcome,
                'acceptance_criteria' => $data->acceptanceCriteria,
                'status' => DevelopmentTaskStatus::Submitted,
                'priority' => $data->priority,
                'submitter_id' => $actor->getKey(),
                'submitted_at' => now(),
            ]));

            return $task instanceof DevelopmentTask
                ? $task
                : throw new LogicException('Development task could not be submitted.');
        });
    }
}
