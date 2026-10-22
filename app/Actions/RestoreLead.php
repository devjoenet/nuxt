<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\User;
use SoutheastCode\Crm\Leads\Models\Lead;
use Spatie\Activitylog\Facades\Activity;

class RestoreLead
{
    public function handle(Lead $lead, User $actor): Lead
    {
        Activity::defaultCauser($actor, fn (): bool => $lead->update(['archived_at' => null]));

        return $lead->refresh();
    }
}
