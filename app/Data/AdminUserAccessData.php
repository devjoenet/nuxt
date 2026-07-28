<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Dto;
use UnexpectedValueException;

final class AdminUserAccessData extends Dto
{
    public function __construct(
        public readonly string $role,
        public readonly bool $isActive,
    ) {}

    /** @param array<string, mixed> $attributes */
    public static function fromValidated(array $attributes): self
    {
        $role = $attributes['role'] ?? null;
        $isActive = $attributes['is_active'] ?? null;

        if (! is_string($role)) {
            throw new UnexpectedValueException('Admin user access data requires a string role value.');
        }

        if (! is_bool($isActive)) {
            throw new UnexpectedValueException('Admin user access data requires a boolean is_active value.');
        }

        return new self($role, $isActive);
    }
}
