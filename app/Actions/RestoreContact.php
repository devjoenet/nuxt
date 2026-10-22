<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\User;
use SoutheastCode\Crm\Contacts\Models\Contact;
use Spatie\Activitylog\Facades\Activity;

class RestoreContact
{
    public function handle(Contact $contact, User $actor): Contact
    {
        Activity::defaultCauser($actor, fn (): bool => $contact->update(['archived_at' => null]));

        return $contact->refresh();
    }
}
