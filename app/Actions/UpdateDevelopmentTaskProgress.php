<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\DevelopmentTaskProgressData;
use App\Data\DevelopmentTaskTransitionData;
use App\Models\DevelopmentTask;
use App\Models\User;

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
        return $this->transitionDevelopmentTask->handle(
            $task,
            new DevelopmentTaskTransitionData($data->status),
            $actor,
        );
    }
}
