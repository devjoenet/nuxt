<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\CrmActivityType;
use App\Models\Lead;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class UpdateLead
{
    public function handle(Lead $lead, array $attributes): Lead
    {
        return DB::transaction(function () use ($lead, $attributes): Lead {
            $lead->contact->update([
                'name' => $attributes['contact_name'],
                'email' => $attributes['contact_email'],
                'phone' => $attributes['contact_phone'] ?? null,
            ]);
            $lead->update(Arr::only($attributes, ['owner_id', 'status', 'source', 'service_interest', 'budget_range', 'timeline', 'project_details']));
            $lead->activities()->create([
                'user_id' => auth()->id(),
                'type' => CrmActivityType::LeadUpdated,
                'description' => 'Lead details updated.',
            ]);

            return $lead->refresh();
        });
    }
}
