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
            $serviceInterests = array_values($attributes['service_interest'] ?? []);
            $updates = Arr::only($attributes, ['status', 'source', 'budget_range', 'timeline', 'project_details']);
            $updates['service_interest'] = $serviceInterests[0] ?? null;
            $updates['service_interests'] = $serviceInterests;

            if (auth()->user()?->isCrmAdministrator() === true && array_key_exists('owner_id', $attributes)) {
                $updates['owner_id'] = $attributes['owner_id'];
            }

            $lead->update($updates);
            $lead->activities()->create([
                'user_id' => auth()->id(),
                'type' => CrmActivityType::LeadUpdated,
                'description' => 'Lead details updated.',
            ]);

            return $lead->refresh();
        });
    }
}
