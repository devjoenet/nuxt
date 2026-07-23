<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Dto;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use UnexpectedValueException;

#[MapInputName(SnakeCaseMapper::class)]
final class LeadData extends Dto
{
    public function __construct(
        public readonly string $contactId,
        public readonly string $status,
        public readonly string $source,
        public readonly string $projectDetails,
        public readonly ?string $organizationId = null,
        public readonly ?int $ownerId = null,
        public readonly ?string $budgetRange = null,
        public readonly ?string $timeline = null,
        /** @var array<int, string> */
        #[MapInputName('service_interest')]
        public readonly array $serviceInterest = [],
        public readonly bool $ownerIdWasProvided = false,
    ) {}

    /**
     * @param  array<string, mixed>  $attributes
     */
    public static function fromValidated(array $attributes): self
    {
        $attributes['owner_id_was_provided'] = array_key_exists('owner_id', $attributes);

        $data = self::factory()->ignoreMagicalMethod('fromValidated')->from($attributes);

        if (! $data instanceof self) {
            throw new UnexpectedValueException('Lead data could not be created.');
        }

        return $data;
    }
}
