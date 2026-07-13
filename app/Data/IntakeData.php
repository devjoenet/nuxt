<?php

declare(strict_types=1);

namespace App\Data;

use App\Http\Requests\StorePublicIntakeRequest;

readonly class IntakeData
{
    public function __construct(
        public string $name,
        public string $email,
        public string $projectDetails,
        public ?string $company,
        public ?string $phone,
        public ?string $website,
        public ?string $budgetRange,
        public ?string $timeline,
        public ?string $serviceInterest,
        public ?string $referralSource,
        public ?string $landingPage,
        public ?string $referrer,
        public ?string $utmSource,
        public ?string $utmMedium,
        public ?string $utmCampaign,
        public ?string $utmTerm,
        public ?string $utmContent,
        public ?string $ipAddress,
        public ?string $userAgent,
    ) {}

    public static function fromRequest(StorePublicIntakeRequest $request): self
    {
        return new self(
            name: $request->string('name')->trim()->toString(),
            email: $request->string('email')->lower()->trim()->toString(),
            projectDetails: $request->string('project_details')->trim()->toString(),
            company: self::nullableString($request, 'company'),
            phone: self::nullableString($request, 'phone'),
            website: self::nullableString($request, 'website'),
            budgetRange: self::nullableString($request, 'budget_range'),
            timeline: self::nullableString($request, 'timeline'),
            serviceInterest: self::nullableString($request, 'service_interest'),
            referralSource: self::nullableString($request, 'referral_source'),
            landingPage: self::nullableString($request, 'landing_page'),
            referrer: self::nullableString($request, 'referrer'),
            utmSource: self::nullableString($request, 'utm_source'),
            utmMedium: self::nullableString($request, 'utm_medium'),
            utmCampaign: self::nullableString($request, 'utm_campaign'),
            utmTerm: self::nullableString($request, 'utm_term'),
            utmContent: self::nullableString($request, 'utm_content'),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );
    }

    private static function nullableString(StorePublicIntakeRequest $request, string $key): ?string
    {
        $value = $request->string($key)->trim()->toString();

        return $value === '' ? null : $value;
    }
}
