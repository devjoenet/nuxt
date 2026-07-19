<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\CrmActivityType;
use App\Models\Contact;
use App\Models\Lead;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class CreateLead
{
    /** @param array<string, mixed> $attributes */
    public function handle(array $attributes, User $user): Lead
    {
        return DB::transaction(function () use ($attributes, $user): Lead {
            $ownerId = $user->id;
            $requestedOwnerId = filter_var($attributes['owner_id'] ?? null, FILTER_VALIDATE_INT);

            if ($user->isCrmAdministrator() && $requestedOwnerId !== false) {
                $ownerId = $requestedOwnerId;
            }
            $organization = filled($attributes['organization_name'] ?? null)
                ? Organization::query()->firstOrCreate(['name' => $attributes['organization_name']], ['website' => $attributes['organization_website'] ?? null])
                : null;

            $contact = Contact::query()->updateOrCreate(
                ['email' => $attributes['contact_email']],
                ['name' => $attributes['contact_name'], 'phone' => $attributes['contact_phone'] ?? null, 'organization_id' => $organization?->id],
            );

            $organization?->update(['owner_id' => $organization->owner_id ?? $ownerId]);
            $contact->update(['owner_id' => $contact->owner_id ?? $ownerId]);

            $serviceInterests = array_values($attributes['service_interest'] ?? []);

            $lead = Lead::query()->create([
                ...Arr::only($attributes, ['status', 'source', 'budget_range', 'timeline', 'project_details']),
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
