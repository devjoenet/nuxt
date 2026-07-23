<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Dto;
use UnexpectedValueException;

final class CrmOptionSearchData extends Dto
{
    public function __construct(public readonly string $query) {}

    /** @param array<string, mixed> $attributes */
    public static function fromValidated(array $attributes): self
    {
        $data = self::factory()->ignoreMagicalMethod('fromValidated')->from($attributes);

        if (! $data instanceof self) {
            throw new UnexpectedValueException('CRM option search data could not be created.');
        }

        return $data;
    }
}
