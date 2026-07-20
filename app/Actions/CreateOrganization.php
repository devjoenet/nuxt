<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\OrganizationData;
use App\Models\Organization;
use App\Models\User;

class CreateOrganization
{
    public function handle(OrganizationData $data, User $user): Organization
    {
        return Organization::query()->create([
            'name' => $data->name,
            'website' => $data->website,
            'owner_id' => $user->isCrmAdministrator() ? ($data->ownerId ?? $user->id) : $user->id,
        ]);
    }
}
