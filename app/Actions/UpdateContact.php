<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\ContactData;
use App\Models\Contact;
use App\Models\Organization;
use App\Models\User;

class UpdateContact
{
    public function handle(Contact $contact, ContactData $data, User $user): Contact
    {
        $organization = $this->organization($data->organizationId, $user);

        $contact->update([
            'name' => $data->name,
            'email' => $data->email,
            'phone' => $data->phone,
            'organization_id' => $organization?->id,
            ...($user->isCrmAdministrator() && $data->ownerIdWasProvided
                ? ['owner_id' => $data->ownerId]
                : []),
        ]);

        return $contact->refresh();
    }

    private function organization(?string $organizationId, User $user): ?Organization
    {
        if ($organizationId === null) {
            return null;
        }

        return Organization::query()->visibleTo($user)->where('uuid', $organizationId)->firstOrFail();
    }
}
