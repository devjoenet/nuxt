<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Organization;
use App\Models\User;

class UpdateOrganization
{
    /** @param array<string, mixed> $attributes */
    public function handle(Organization $organization, array $attributes, User $user): Organization
    {
        $organization->update([
            'name' => $attributes['name'],
            'website' => $attributes['website'] ?? null,
            ...($user->isCrmAdministrator() && array_key_exists('owner_id', $attributes)
                ? ['owner_id' => $attributes['owner_id']]
                : []),
        ]);

        return $organization->refresh();
    }
}
