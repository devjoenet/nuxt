<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\DevelopmentRequestType;
use App\Enums\DevelopmentTaskArea;
use App\Enums\TaskPriority;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Dto;
use UnexpectedValueException;

final class DevelopmentTaskData extends Dto
{
    public function __construct(
        public readonly string $title,
        public readonly DevelopmentRequestType $requestType,
        public readonly DevelopmentTaskArea $affectedArea,
        public readonly string $description,
        public readonly string $desiredOutcome,
        public readonly string $acceptanceCriteria,
        public readonly TaskPriority $priority = TaskPriority::Normal,
    ) {}

    /** @param array<string, mixed> $attributes */
    public static function fromRaw(array $attributes): self
    {
        $normalized = self::normalize($attributes);

        return self::fromValidated(validator($normalized, self::rules())->validate());
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private static function normalize(array $attributes): array
    {
        foreach ([
            'title',
            'request_type',
            'affected_area',
            'description',
            'desired_outcome',
            'acceptance_criteria',
            'priority',
        ] as $key) {
            if (is_string($attributes[$key] ?? null)) {
                $attributes[$key] = mb_trim($attributes[$key]);
            }
        }

        return $attributes;
    }

    /** @param array<mixed> $attributes */
    public static function fromValidated(array $attributes): self
    {
        return new self(
            title: self::stringValue($attributes, 'title'),
            requestType: DevelopmentRequestType::from(self::stringValue($attributes, 'request_type')),
            affectedArea: DevelopmentTaskArea::from(self::stringValue($attributes, 'affected_area')),
            description: self::stringValue($attributes, 'description'),
            desiredOutcome: self::stringValue($attributes, 'desired_outcome'),
            acceptanceCriteria: self::stringValue($attributes, 'acceptance_criteria'),
            priority: TaskPriority::from(self::stringValue($attributes, 'priority', TaskPriority::Normal->value)),
        );
    }

    /** @param array<mixed> $attributes */
    private static function stringValue(array $attributes, string $key, ?string $default = null): string
    {
        $value = $attributes[$key] ?? $default;

        if (! is_string($value)) {
            throw new UnexpectedValueException("Development task data requires a string {$key} value.");
        }

        return mb_trim($value);
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public static function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'request_type' => ['required', Rule::enum(DevelopmentRequestType::class)],
            'affected_area' => ['required', Rule::enum(DevelopmentTaskArea::class)],
            'description' => ['required', 'string', 'max:20000'],
            'desired_outcome' => ['required', 'string', 'max:10000'],
            'acceptance_criteria' => ['required', 'string', 'max:20000'],
            'priority' => ['sometimes', Rule::enum(TaskPriority::class)],
        ];
    }
}
