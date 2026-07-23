<?php

declare(strict_types=1);

namespace App\Data;

final readonly class ActivityLogFiltersData
{
    /**
     * @param  array<int, string>  $events
     * @param  array<int, string>  $causers
     * @param  array<int, string>  $subjects
     */
    public function __construct(
        public ?string $search,
        public array $events,
        public array $causers,
        public array $subjects,
        public ?string $from,
        public ?string $to,
    ) {}

    /** @param array<string, mixed> $attributes */
    public static function fromValidated(array $attributes): self
    {
        return new self(
            search: self::nullableString($attributes['search'] ?? null),
            events: self::strings($attributes['event'] ?? []),
            causers: self::strings($attributes['causer'] ?? []),
            subjects: self::strings($attributes['subject'] ?? []),
            from: self::nullableString($attributes['from'] ?? null),
            to: self::nullableString($attributes['to'] ?? null),
        );
    }

    private static function nullableString(mixed $value): ?string
    {
        return is_string($value) ? $value : null;
    }

    /** @return array<int, string> */
    private static function strings(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        return array_values(array_filter($value, is_string(...)));
    }

    /** @return array{search: ?string, event: array<int, string>, causer: array<int, string>, subject: array<int, string>, from: ?string, to: ?string} */
    public function filters(): array
    {
        return [
            'search' => $this->search,
            'event' => $this->events,
            'causer' => $this->causers,
            'subject' => $this->subjects,
            'from' => $this->from,
            'to' => $this->to,
        ];
    }
}
