<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\LeadData;
use App\Enums\CrmActivityType;
use App\Models\Contact;
use App\Models\Lead;
use App\Models\Organization;
use App\Models\User;
use App\Support\CrmAccess;
use Illuminate\Support\Facades\DB;

class UpdateLead
{
    public function handle(Lead $lead, LeadData $data, User $user): Lead
    {
        return DB::transaction(function () use ($lead, $data, $user): Lead {
            $contact = Contact::query()->visibleTo($user)->where('uuid', $data->contactId)->firstOrFail();
            $organization = $data->organizationId === null
                ? null
                : Organization::query()->visibleTo($user)->where('uuid', $data->organizationId)->firstOrFail();
            $serviceInterests = array_values($data->serviceInterest);
            $updates = [
                'status' => $data->status,
                'source' => $data->source,
                'budget_range' => $data->budgetRange,
                'timeline' => $data->timeline,
                'project_details' => $data->projectDetails,
                'contact_id' => $contact->id,
                'organization_id' => $organization?->id,
            ];
            $updates['service_interest'] = $serviceInterests[0] ?? null;
            $updates['service_interests'] = $serviceInterests;

            if (CrmAccess::administrator($user) && $data->ownerIdWasProvided) {
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
