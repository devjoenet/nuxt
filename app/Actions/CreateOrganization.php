<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\OrganizationData;
use App\Models\User;
use SoutheastCode\Crm\Organizations\Models\Organization;
use SoutheastCode\Crm\Shared\Support\CrmAccess;

class CreateOrganization
{
    public function handle(OrganizationData $data, User $user): Organization
    {
        return Organization::query()->create([
            'name' => $data->name,
            'website' => $data->website,
            'owner_id' => CrmAccess::administrator($user) ? ($data->ownerId ?? $user->id) : $user->id,
        ]);
    }
}
