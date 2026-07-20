<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\LeadData;
use App\Enums\CrmActivityType;
use App\Models\Contact;
use App\Models\Lead;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreateLead
{
    public function handle(LeadData $data, User $user): Lead
    {
        return DB::transaction(function () use ($data, $user): Lead {
            $ownerId = $user->id;

            if ($user->isCrmAdministrator() && $data->ownerId !== null) {
                $ownerId = $data->ownerId;
            }

            $organization = filled($data->organizationName)
                ? Organization::query()->firstOrCreate(['name' => $data->organizationName], ['website' => $data->organizationWebsite])
                : null;

            $contact = Contact::query()->updateOrCreate(
                ['email' => $data->contactEmail],
                ['name' => $data->contactName, 'phone' => $data->contactPhone, 'organization_id' => $organization?->id],
            );

            $organization?->update(['owner_id' => $organization->owner_id ?? $ownerId]);
            $contact->update(['owner_id' => $contact->owner_id ?? $ownerId]);

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
                'consented_at' => now(),
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
