<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\ContactData;
use App\Models\Contact;
use App\Models\Organization;
use App\Models\User;

class CreateContact
{
    public function handle(ContactData $data, User $user): Contact
    {
        $organization = $this->organization($data->organizationId, $user);

        return Contact::query()->create([
            'name' => $data->name,
            'email' => $data->email,
            'phone' => $data->phone,
            'organization_id' => $organization?->id,
            'owner_id' => $user->isCrmAdministrator() ? ($data->ownerId ?? $user->id) : $user->id,
        ]);
    }

    private function organization(?int $organizationId, User $user): ?Organization
    {
        if ($organizationId === null) {
            return null;
        }

        $organization = Organization::query()->findOrFail($organizationId);
        abort_unless($organization->isVisibleTo($user), 404);

        return $organization;
    }
}
