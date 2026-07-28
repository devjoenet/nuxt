<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\LeadNoteData;
use App\Enums\CrmActivityType;
use App\Models\Lead;
use App\Models\Note;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use LogicException;
use Spatie\Activitylog\Facades\Activity;

class CreateLeadNote
{
    public function handle(Lead $lead, LeadNoteData $data, User $actor): Note
    {
        return DB::transaction(function () use ($lead, $data, $actor): Note {
            $note = Activity::defaultCauser($actor, fn (): Note => $lead->notes()->create([
                'body' => $data->body,
                'user_id' => $actor->id,
            ]));
            $note = $note instanceof Note ? $note : throw new LogicException('Lead note could not be created.');

            Activity::defaultCauser($actor, fn () => $lead->activities()->create([
                'user_id' => $actor->id,
                'type' => CrmActivityType::NoteAdded,
                'description' => 'A note was added.',
            ]));

            return $note;
        });
    }
}
