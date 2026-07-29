<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\DevelopmentTaskNoteData;
use App\Enums\DevelopmentNoteAudience;
use App\Models\DevelopmentTask;
use App\Models\DevelopmentTaskNote;
use App\Models\User;
use App\Notifications\DevelopmentTaskClarificationAdded;
use Illuminate\Support\Facades\DB;

class AddDevelopmentTaskNote
{
    public function handle(
        DevelopmentTask $task,
        DevelopmentTaskNoteData $data,
        User $actor,
    ): DevelopmentTaskNote {
        $note = DB::transaction(fn (): DevelopmentTaskNote => $task->notes()->create([
            'author_id' => $actor->getKey(),
            'audience' => $data->audience,
            'body' => $data->body,
        ]));

        if ($data->audience === DevelopmentNoteAudience::Participants) {
            $this->notifyOtherParticipant($task, $actor, $data->body);
        }

        return $note;
    }

    private function notifyOtherParticipant(DevelopmentTask $task, User $actor, string $body): void
    {
        $recipient = $task->submitter_id === $actor->getKey()
            ? $task->reviewer
            : $task->submitter;

        if (! $recipient instanceof User || $recipient->is($actor)) {
            return;
        }

        $recipient->notify(new DevelopmentTaskClarificationAdded(
            taskUuid: $task->uuid,
            taskTitle: $task->title,
            authorName: $actor->name,
            body: $body,
        ));
    }
}
