<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\DevelopmentTaskLinkData;
use App\Models\DevelopmentTask;
use App\Models\DevelopmentTaskLink;
use App\Models\User;

class AttachDevelopmentTaskLink
{
    /** @param array<string, mixed> $attributes */
    public function handleRaw(DevelopmentTask $task, array $attributes, User $actor): DevelopmentTaskLink
    {
        return $this->handle($task, DevelopmentTaskLinkData::fromRaw($attributes), $actor);
    }

    public function handle(
        DevelopmentTask $task,
        DevelopmentTaskLinkData $data,
        User $actor,
    ): DevelopmentTaskLink {
        return $task->links()->create([
            'added_by_id' => $actor->getKey(),
            'type' => $data->type,
            'label' => $data->label,
            'url' => $data->url,
        ])->load('addedBy');
    }
}
