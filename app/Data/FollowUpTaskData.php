<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\TaskPriority;
use Illuminate\Support\Carbon;
use Spatie\LaravelData\Dto;
use UnexpectedValueException;

final class FollowUpTaskData extends Dto
{
    public function __construct(
        public readonly string $title,
        public readonly ?string $description,
        public readonly TaskPriority $priority,
        public readonly Carbon $dueAt,
    ) {}

    /** @param array<string, mixed> $attributes */
    public static function fromValidated(array $attributes): self
    {
        $priority = self::stringValue($attributes, 'priority');

        return new self(
            title: self::stringValue($attributes, 'title'),
            description: self::nullableStringValue($attributes, 'description'),
            priority: TaskPriority::from($priority),
            dueAt: Carbon::parse(self::stringValue($attributes, 'due_at')),
        );
    }

    /** @param array<string, mixed> $attributes */
    private static function stringValue(array $attributes, string $key): string
    {
        $value = $attributes[$key] ?? null;

        if (! is_string($value)) {
            throw new UnexpectedValueException("Follow-up task data requires a string {$key} value.");
        }

        return $value;
    }

    /** @param array<string, mixed> $attributes */
    private static function nullableStringValue(array $attributes, string $key): ?string
    {
        $value = $attributes[$key] ?? null;

        if ($value === null) {
            return null;
        }

        if (! is_string($value)) {
            throw new UnexpectedValueException("Follow-up task data requires a string or null {$key} value.");
        }

        return $value;
    }
}
