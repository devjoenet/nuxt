<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\DevelopmentTaskTransitionData;
use App\Models\DevelopmentTask;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Spatie\Activitylog\Facades\Activity;

class TransitionDevelopmentTask
{
    public function __construct(private ArchiveDevelopmentTask $archiveDevelopmentTask) {}

    /** @param array<string, mixed> $attributes */
    public function handleRaw(DevelopmentTask $task, array $attributes, User $actor): DevelopmentTask
    {
        return $this->handle($task, DevelopmentTaskTransitionData::fromRaw($attributes), $actor);
    }

    public function handle(
        DevelopmentTask $task,
        DevelopmentTaskTransitionData $data,
        User $actor,
    ): DevelopmentTask {
        return DB::transaction(function () use ($task, $data, $actor): DevelopmentTask {
            $task = DevelopmentTask::query()
                ->withTrashed()
                ->whereKey($task->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($task->status === $data->status) {
                return $task;
            }

            if ($task->trashed()) {
                throw ValidationException::withMessages([
                    'status' => 'Archived development tasks cannot transition.',
                ]);
            }

            if (! $task->status->canTransitionTo($data->status)) {
                throw ValidationException::withMessages([
                    'status' => "A {$task->status->value} task cannot transition to {$data->status->value}.",
                ]);
            }

            $timestampColumn = $data->status->timestampColumn();
            $attributes = ['status' => $data->status];

            if ($task->getAttribute($timestampColumn) === null) {
                $attributes[$timestampColumn] = now();
            }

            Activity::defaultCauser($actor, function () use ($task, $attributes, $data, $actor): void {
                $task->update($attributes);

                if ($data->status->isTerminal()) {
                    $this->archiveDevelopmentTask->handle($task, $actor);
                }
            });

            return $task->refresh();
        });
    }
}
