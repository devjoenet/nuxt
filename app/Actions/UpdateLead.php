<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\LeadData;
use App\Enums\CrmActivityType;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UpdateLead
{
    public function handle(Lead $lead, LeadData $data, User $user): Lead
    {
        return DB::transaction(function () use ($lead, $data, $user): Lead {
            $lead->contact->update([
                'name' => $data->contactName,
                'email' => $data->contactEmail,
                'phone' => $data->contactPhone,
            ]);
            $serviceInterests = array_values($data->serviceInterest);
            $updates = [
                'status' => $data->status,
                'source' => $data->source,
                'budget_range' => $data->budgetRange,
                'timeline' => $data->timeline,
                'project_details' => $data->projectDetails,
            ];
            $updates['service_interest'] = $serviceInterests[0] ?? null;
            $updates['service_interests'] = $serviceInterests;

            if ($user->isCrmAdministrator() && $data->ownerIdWasProvided) {
                $updates['owner_id'] = $data->ownerId;
            }

            $lead->update($updates);
            $lead->activities()->create([
                'user_id' => $user->id,
                'type' => CrmActivityType::LeadUpdated,
                'description' => 'Lead details updated.',
            ]);

            return $lead->refresh();
        });
    }
}
