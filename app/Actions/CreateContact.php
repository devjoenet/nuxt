<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Contact;
use App\Models\Organization;
use App\Models\User;

class CreateContact
{
    /** @param array<string, mixed> $attributes */
    public function handle(array $attributes, User $user): Contact
    {
        $organization = $this->organization($attributes['organization_id'] ?? null, $user);

        return Contact::query()->create([
            'name' => $attributes['name'],
            'email' => $attributes['email'],
            'phone' => $attributes['phone'] ?? null,
            'organization_id' => $organization?->id,
            'owner_id' => $user->isCrmAdministrator() ? ($attributes['owner_id'] ?? $user->id) : $user->id,
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
