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
        /** @var array<int, string> */
        public array $serviceInterests,
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
        return self::fromArray($request->validated(), $request->ip(), $request->userAgent());
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public static function fromArray(array $attributes, ?string $ipAddress = null, ?string $userAgent = null): self
    {
        return new self(
            name: str($attributes['name'] ?? '')->trim()->toString(),
            email: str($attributes['email'] ?? '')->lower()->trim()->toString(),
            projectDetails: str($attributes['project_details'] ?? '')->trim()->toString(),
            company: self::nullableString($attributes, 'company'),
            phone: self::nullableString($attributes, 'phone'),
            website: self::nullableString($attributes, 'website'),
            budgetRange: self::nullableString($attributes, 'budget_range'),
            timeline: self::nullableString($attributes, 'timeline'),
            serviceInterests: array_values($attributes['service_interest'] ?? []),
            referralSource: self::nullableString($attributes, 'referral_source'),
            landingPage: self::nullableString($attributes, 'landing_page'),
            referrer: self::nullableString($attributes, 'referrer'),
            utmSource: self::nullableString($attributes, 'utm_source'),
            utmMedium: self::nullableString($attributes, 'utm_medium'),
            utmCampaign: self::nullableString($attributes, 'utm_campaign'),
            utmTerm: self::nullableString($attributes, 'utm_term'),
            utmContent: self::nullableString($attributes, 'utm_content'),
            ipAddress: $ipAddress,
            userAgent: $userAgent,
        );
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private static function nullableString(array $attributes, string $key): ?string
    {
        $value = str($attributes[$key] ?? '')->trim()->toString();

        return $value === '' ? null : $value;
    }
}
