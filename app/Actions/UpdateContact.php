<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\ContactData;
use App\Models\User;
use SoutheastCode\Crm\Contacts\Models\Contact;
use SoutheastCode\Crm\Organizations\Models\Organization;
use SoutheastCode\Crm\Shared\Support\CrmAccess;

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
            ...(CrmAccess::administrator($user) && $data->ownerIdWasProvided
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
