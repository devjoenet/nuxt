<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\User;
use SoutheastCode\Crm\Organizations\Models\Organization;
use Spatie\Activitylog\Facades\Activity;

class DeleteOrganization
{
    public function handle(Organization $organization, User $actor): Organization
    {
        Activity::defaultCauser($actor, fn (): ?bool => $organization->delete());

        return $organization->refresh();
    }
}
