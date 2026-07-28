<?php

declare(strict_types=1);

namespace App\Data;

use App\Http\Requests\StorePublicIntakeRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Dto;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use UnexpectedValueException;

#[MapInputName(SnakeCaseMapper::class)]
final class IntakeData extends Dto
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly string $projectDetails,
        public readonly ?string $company = null,
        public readonly ?string $phone = null,
        public readonly ?string $website = null,
        public readonly ?string $budgetRange = null,
        public readonly ?string $timeline = null,
        /** @var array<int, string> */
        #[MapInputName('service_interest')]
        public readonly array $serviceInterests = [],
        public readonly ?string $referralSource = null,
        public readonly ?string $landingPage = null,
        public readonly ?string $referrer = null,
        public readonly ?string $utmSource = null,
        public readonly ?string $utmMedium = null,
        public readonly ?string $utmCampaign = null,
        public readonly ?string $utmTerm = null,
        public readonly ?string $utmContent = null,
        public readonly ?string $ipAddress = null,
        public readonly ?string $userAgent = null,
        public readonly ?string $consent = null,
        public readonly ?string $websiteConfirmation = null,
    ) {}

    public static function fromRequest(StorePublicIntakeRequest $request): self
    {
        $data = self::factory()->ignoreMagicalMethod('fromRequest')->from([
            ...$request->validated(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return $data instanceof self ? $data : throw new UnexpectedValueException('Intake data could not be created.');
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public static function rules(): array
    {
        return (new StorePublicIntakeRequest)->rules();
    }
}
