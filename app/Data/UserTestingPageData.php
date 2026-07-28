<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Dto;
use UnexpectedValueException;

final class UserTestingPageData extends Dto
{
    public readonly ?bool $tested;

    public readonly ?bool $approved;

    public readonly ?bool $roadmapTask;

    public readonly ?string $notes;

    public function __construct(
        ?bool $tested,
        ?bool $approved,
        ?bool $roadmapTask,
        ?string $notes,
    ) {
        $this->tested = $tested;
        $this->approved = $approved;
        $this->roadmapTask = $roadmapTask;
        $this->notes = $notes === null || ! filled($notes) ? null : mb_trim($notes);
    }

    /** @param array<string, mixed> $attributes */
    public static function fromValidated(array $attributes): self
    {
        return new self(
            self::boolean($attributes['tested'] ?? null),
            self::boolean($attributes['approved'] ?? null),
            self::boolean($attributes['roadmap_task'] ?? null),
            self::notes($attributes['notes'] ?? null),
        );
    }

    private static function boolean(mixed $value): ?bool
    {
        if ($value === null) {
            return null;
        }

        if (is_bool($value)) {
            return $value;
        }

        $normalized = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        if (is_bool($normalized)) {
            return $normalized;
        }

        throw new UnexpectedValueException('User testing page data requires boolean state values.');
    }

    private static function notes(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (! is_string($value)) {
            throw new UnexpectedValueException('User testing page data requires string notes.');
        }

        return filled($value) ? mb_trim($value) : null;
    }
}
