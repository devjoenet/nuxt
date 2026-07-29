<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\DevelopmentTask;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Spatie\Activitylog\Facades\Activity;

class ArchiveDevelopmentTask
{
    public function handle(DevelopmentTask $task, User $actor): DevelopmentTask
    {
        return DB::transaction(function () use ($task, $actor): DevelopmentTask {
            $task = DevelopmentTask::query()
                ->withTrashed()
                ->whereKey($task->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($task->trashed()) {
                return $task;
            }

            if (! $task->status->isTerminal()) {
                throw ValidationException::withMessages([
                    'status' => 'Only completed, rejected, or cancelled development tasks can be archived.',
                ]);
            }

            Activity::defaultCauser($actor, fn (): ?bool => $task->delete());

            return $task->refresh();
        });
    }
}
