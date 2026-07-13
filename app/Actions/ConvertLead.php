<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\CrmActivityType;
use App\Enums\FollowUpTaskStatus;
use App\Enums\LeadStatus;
use App\Models\Lead;
use Illuminate\Support\Facades\DB;

class ConvertLead
{
    public function handle(Lead $lead): Lead
    {
        return DB::transaction(function () use ($lead): Lead {
            $organization = $lead->organization;

            abort_if($organization === null, 422, 'A lead must have an organization before conversion.');

            if ($lead->converted_at !== null) {
                return $lead;
            }

            $organization->update(['client_since_at' => $organization->client_since_at ?? now()]);
            $lead->update(['status' => LeadStatus::Won, 'converted_at' => now()]);
            $lead->followUpTasks()->where('status', FollowUpTaskStatus::Open)->update([
                'status' => FollowUpTaskStatus::Completed,
                'completed_at' => now(),
            ]);
            $lead->activities()->create([
                'user_id' => auth()->id(),
                'type' => CrmActivityType::LeadConverted,
                'description' => 'Lead converted to a client.',
            ]);

            return $lead->refresh();
        });
    }
}
