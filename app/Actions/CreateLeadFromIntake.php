<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\IntakeData;
use App\Models\Contact;
use App\Models\Lead;
use App\Models\Organization;
use App\Notifications\NewLeadReceived;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

use function config;
use function now;

class CreateLeadFromIntake
{
    public function handle(IntakeData $data): Lead
    {
        $lead = DB::transaction(function () use ($data): Lead {
            $organization = $this->organization($data);

            $contact = Contact::query()->updateOrCreate(
                ['email' => $data->email],
                [
                    'organization_id' => $organization?->id,
                    'name' => $data->name,
                    'phone' => $data->phone,
                ],
            );

            $lead = Lead::query()->create([
                'contact_id' => $contact->id,
                'organization_id' => $organization?->id,
                'status' => 'new',
                'source' => 'website',
                'service_interest' => $data->serviceInterest,
                'budget_range' => $data->budgetRange,
                'timeline' => $data->timeline,
                'project_details' => $data->projectDetails,
                'referral_source' => $data->referralSource,
                'landing_page' => $data->landingPage,
                'referrer' => $data->referrer,
                'utm_source' => $data->utmSource,
                'utm_medium' => $data->utmMedium,
                'utm_campaign' => $data->utmCampaign,
                'utm_term' => $data->utmTerm,
                'utm_content' => $data->utmContent,
                'ip_address' => $data->ipAddress,
                'user_agent' => $data->userAgent,
                'consented_at' => now(),
            ]);

            $lead->activities()->create([
                'type' => 'public_intake_created',
                'description' => 'Lead created from the public intake form.',
                'properties' => ['source' => 'website'],
            ]);

            $lead->followUpTasks()->create([
                'title' => 'Follow up on website inquiry',
                'description' => 'Review the inquiry and make initial contact.',
                'due_at' => now()->addWeekday()->setTime(17, 0),
            ]);

            return $lead->load('contact', 'organization');
        });

        Notification::route('mail', (string) config('services.leads.recipient'))
            ->notify(new NewLeadReceived($lead)->afterCommit());

        return $lead;
    }

    private function organization(IntakeData $data): ?Organization
    {
        if ($data->company === null && $data->website === null) {
            return null;
        }

        $identity = $data->website !== null ? ['website' => $data->website] : ['name' => $data->company];

        return Organization::query()->firstOrCreate($identity, [
            'name' => $data->company ?? $data->website,
            'website' => $data->website,
        ]);
    }
}
