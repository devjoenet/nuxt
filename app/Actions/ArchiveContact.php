<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Contact;
use App\Models\User;
use Spatie\Activitylog\Facades\Activity;

class ArchiveContact
{
    public function handle(Contact $contact, User $actor): Contact
    {
        Activity::defaultCauser($actor, fn (): bool => $contact->update(['archived_at' => now()]));

        return $contact->refresh();
    }
}
