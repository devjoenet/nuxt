<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Contact;
use App\Models\Organization;
use App\Models\User;

class UpdateContact
{
    /** @param array<string, mixed> $attributes */
    public function handle(Contact $contact, array $attributes, User $user): Contact
    {
        $organization = $this->organization($attributes['organization_id'] ?? null, $user);

        $contact->update([
            'name' => $attributes['name'],
            'email' => $attributes['email'],
            'phone' => $attributes['phone'] ?? null,
            'organization_id' => $organization?->id,
            ...($user->isCrmAdministrator() && array_key_exists('owner_id', $attributes)
                ? ['owner_id' => $attributes['owner_id']]
                : []),
        ]);

        return $contact->refresh();
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
