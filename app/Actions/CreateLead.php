<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\CrmActivityType;
use App\Models\Contact;
use App\Models\Lead;
use App\Models\Organization;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class CreateLead
{
    public function handle(array $attributes): Lead
    {
        return DB::transaction(function () use ($attributes): Lead {
            $organization = filled($attributes['organization_name'] ?? null)
                ? Organization::query()->firstOrCreate(['name' => $attributes['organization_name']], ['website' => $attributes['organization_website'] ?? null])
                : null;

            $contact = Contact::query()->updateOrCreate(
                ['email' => $attributes['contact_email']],
                ['name' => $attributes['contact_name'], 'phone' => $attributes['contact_phone'] ?? null, 'organization_id' => $organization?->id],
            );

            $lead = Lead::query()->create([
                ...Arr::only($attributes, ['owner_id', 'status', 'source', 'service_interest', 'budget_range', 'timeline', 'project_details']),
                'contact_id' => $contact->id,
                'organization_id' => $organization?->id,
                'consented_at' => now(),
            ]);

            $lead->activities()->create([
                'user_id' => auth()->id(),
                'type' => CrmActivityType::LeadCreated,
                'description' => 'Lead created in the CRM workspace.',
            ]);

            return $lead;
        });
    }
}
