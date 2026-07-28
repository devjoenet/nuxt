<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Dto;
use UnexpectedValueException;

final class LeadNoteData extends Dto
{
    public function __construct(public readonly string $body) {}

    /** @param array<string, mixed> $attributes */
    public static function fromValidated(array $attributes): self
    {
        $body = $attributes['body'] ?? null;

        if (! is_string($body)) {
            throw new UnexpectedValueException('Lead note data requires a string body value.');
        }

        return new self($body);
    }
}
