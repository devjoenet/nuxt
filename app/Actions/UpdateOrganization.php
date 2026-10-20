<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\OrganizationData;
use App\Models\Organization;
use App\Models\User;
use App\Support\CrmAccess;

class UpdateOrganization
{
    public function handle(Organization $organization, OrganizationData $data, User $user): Organization
    {
        $organization->update([
            'name' => $data->name,
            'website' => $data->website,
            ...(CrmAccess::administrator($user) && $data->ownerIdWasProvided
                ? ['owner_id' => $data->ownerId]
                : []),
        ]);

        return $organization->refresh();
    }
}
