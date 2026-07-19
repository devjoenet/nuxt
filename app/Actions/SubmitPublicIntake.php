<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\IntakeData;
use App\Http\Requests\StorePublicIntakeRequest;
use App\Models\Lead;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;

class SubmitPublicIntake
{
    public function __construct(
        private readonly CreateLeadFromIntake $createLead,
        private readonly ValidationFactory $validator,
    ) {}

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function handle(array $attributes, ?string $ipAddress = null, ?string $userAgent = null): Lead
    {
        $validated = $this->validator->make(
            StorePublicIntakeRequest::normalize($attributes),
            (new StorePublicIntakeRequest)->rules(),
        )->validate();

        return $this->createLead->handle(IntakeData::fromArray($validated, $ipAddress, $userAgent));
    }
}
