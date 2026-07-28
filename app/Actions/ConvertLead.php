<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\CrmActivityType;
use App\Enums\FollowUpTaskStatus;
use App\Enums\LeadStatus;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Facades\Activity;

class ConvertLead
{
    public function handle(Lead $lead, User $actor): Lead
    {
        return DB::transaction(function () use ($lead, $actor): Lead {
            $organization = $lead->organization;

            abort_if($organization === null, 422, 'A lead must have an organization before conversion.');

            if ($lead->converted_at !== null) {
                return $lead;
            }

            Activity::defaultCauser($actor, function () use ($organization, $lead, $actor): void {
                $organization->update(['client_since_at' => $organization->client_since_at ?? now()]);
                $lead->update(['status' => LeadStatus::Won, 'converted_at' => now()]);
                $lead->followUpTasks()->where('status', FollowUpTaskStatus::Open)->update([
                    'status' => FollowUpTaskStatus::Completed,
                    'completed_at' => now(),
                ]);
                $lead->activities()->create([
                    'user_id' => $actor->id,
                    'type' => CrmActivityType::LeadConverted,
                    'description' => 'Lead converted to a client.',
                ]);
            });

            return $lead->refresh();
        });
    }
}
