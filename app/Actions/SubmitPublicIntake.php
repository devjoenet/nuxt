<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\IntakeData;
use App\Models\BusinessSetting;
use App\Notifications\NewLeadReceived;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use SoutheastCode\Crm\Leads\Actions\CreateLeadFromIntake;
use SoutheastCode\Crm\Leads\Data\IntakeLeadData;
use SoutheastCode\Crm\Leads\Models\Lead;

class SubmitPublicIntake
{
    public function __construct(
        private readonly CreateLeadFromIntake $createLead,
    ) {}

    public function handle(IntakeData $data): Lead
    {
        return DB::transaction(function () use ($data): Lead {
            $lead = $this->createLead->handle(new IntakeLeadData(
                name: $data->name,
                email: $data->email,
                projectDetails: $data->projectDetails,
                consentedAt: CarbonImmutable::now(),
                company: $data->company,
                phone: $data->phone,
                website: $data->website,
                budgetRange: $data->budgetRange,
                timeline: $data->timeline,
                serviceInterests: array_values($data->serviceInterests),
                referralSource: $data->referralSource,
                landingPage: $data->landingPage,
                referrer: $data->referrer,
                utmSource: $data->utmSource,
                utmMedium: $data->utmMedium,
                utmCampaign: $data->utmCampaign,
                utmTerm: $data->utmTerm,
                utmContent: $data->utmContent,
                ipAddress: $data->ipAddress,
                userAgent: $data->userAgent,
            ));

            Notification::route('mail', (string) BusinessSetting::current()->notification_email)
                ->notify(new NewLeadReceived($lead)->afterCommit());

            return $lead;
        });
    }
}
