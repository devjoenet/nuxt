<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\User;
use SoutheastCode\Crm\Organizations\Models\Organization;
use Spatie\Activitylog\Facades\Activity;

class RestoreOrganization
{
    public function handle(Organization $organization, User $actor): Organization
    {
        Activity::defaultCauser($actor, fn (): bool => $organization->update(['archived_at' => null]));

        return $organization->refresh();
    }
}
