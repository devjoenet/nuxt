<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\LeadData;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use SoutheastCode\Crm\Contacts\Models\Contact;
use SoutheastCode\Crm\Leads\Models\Lead;
use SoutheastCode\Crm\Organizations\Models\Organization;
use SoutheastCode\Crm\Shared\Enums\CrmActivityType;
use SoutheastCode\Crm\Shared\Support\CrmAccess;

class CreateLead
{
    public function handle(LeadData $data, User $user): Lead
    {
        return DB::transaction(function () use ($data, $user): Lead {
            $ownerId = $user->id;

            if (CrmAccess::administrator($user) && $data->ownerId !== null) {
                $ownerId = $data->ownerId;
            }

            $contact = Contact::query()->visibleTo($user)->where('uuid', $data->contactId)->firstOrFail();
            $organization = $data->organizationId === null
                ? null
                : Organization::query()->visibleTo($user)->where('uuid', $data->organizationId)->firstOrFail();

            $serviceInterests = array_values($data->serviceInterest);

            $lead = Lead::query()->create([
                'status' => $data->status,
                'source' => $data->source,
                'budget_range' => $data->budgetRange,
                'timeline' => $data->timeline,
                'project_details' => $data->projectDetails,
                'service_interest' => $serviceInterests[0] ?? null,
                'service_interests' => $serviceInterests,
                'owner_id' => $ownerId,
                'contact_id' => $contact->id,
                'organization_id' => $organization?->id,
                'consented_at' => null,
            ]);

            $lead->activities()->create([
                'user_id' => $user->id,
                'type' => CrmActivityType::LeadCreated,
                'description' => 'Lead created in the CRM workspace.',
            ]);

            return $lead;
        });
    }
}
