<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Organization;
use App\Models\User;

class CreateOrganization
{
    /** @param array<string, mixed> $attributes */
    public function handle(array $attributes, User $user): Organization
    {
        return Organization::query()->create([
            'name' => $attributes['name'],
            'website' => $attributes['website'] ?? null,
            'owner_id' => $user->isCrmAdministrator() ? ($attributes['owner_id'] ?? $user->id) : $user->id,
        ]);
    }
}
