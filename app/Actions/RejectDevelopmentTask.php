<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\DevelopmentTaskNoteData;
use App\Data\DevelopmentTaskTransitionData;
use App\Enums\DevelopmentNoteAudience;
use App\Enums\DevelopmentTaskStatus;
use App\Models\DevelopmentTask;
use App\Models\User;
use App\Notifications\DevelopmentTaskRejected;
use Illuminate\Support\Facades\DB;

class RejectDevelopmentTask
{
    public function __construct(private TransitionDevelopmentTask $transitionDevelopmentTask) {}

    public function handle(
        DevelopmentTask $task,
        DevelopmentTaskNoteData $data,
        User $actor,
    ): DevelopmentTask {
        $task = DB::transaction(function () use ($task, $data, $actor): DevelopmentTask {
            $task->notes()->create([
                'author_id' => $actor->getKey(),
                'audience' => DevelopmentNoteAudience::Participants,
                'body' => $data->body,
            ]);

            return $this->transitionDevelopmentTask->handle(
                $task,
                new DevelopmentTaskTransitionData(DevelopmentTaskStatus::Rejected),
                $actor,
            );
        });
        $submitter = $task->submitter;

        if ($submitter instanceof User && ! $submitter->is($actor)) {
            $submitter->notify(new DevelopmentTaskRejected(
                taskTitle: $task->title,
                reason: $data->body,
            ));
        }

        return $task;
    }
}
