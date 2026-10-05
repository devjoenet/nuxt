<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Dto;
use UnexpectedValueException;

final class CrmOptionSearchData extends Dto
{
    public function __construct(public readonly string $query = '', public readonly int $page = 1) {}

    /** @param array<string, mixed> $attributes */
    public static function fromValidated(array $attributes): self
    {
        $query = $attributes['query'] ?? '';

        if (! is_string($query)) {
            throw new UnexpectedValueException('CRM option search query must be a string.');
        }

        $attributes['query'] = mb_trim($query);
        $data = self::factory()->ignoreMagicalMethod('fromValidated')->from($attributes);

        return $data instanceof self ? $data : throw new UnexpectedValueException('CRM option search data could not be created.');
    }
}
