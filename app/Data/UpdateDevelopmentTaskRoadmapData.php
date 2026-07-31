<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\TaskPriority;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Dto;
use UnexpectedValueException;

final class UpdateDevelopmentTaskRoadmapData extends Dto
{
    public function __construct(
        public readonly TaskPriority $priority,
        public readonly ?string $assigneeId,
        public readonly ?int $roadmapPosition,
    ) {}

    /** @param array<string, mixed> $attributes */
    public static function fromRaw(array $attributes): self
    {
        return self::fromValidated(validator($attributes, self::rules())->validate());
    }

    /** @param array<mixed> $attributes */
    public static function fromValidated(array $attributes): self
    {
        $priority = $attributes['priority'] ?? null;
        $assigneeId = $attributes['assignee_id'] ?? null;
        $roadmapPosition = $attributes['roadmap_position'] ?? null;

        if (! is_string($priority)) {
            throw new UnexpectedValueException('Development task roadmap data requires a string priority value.');
        }

        if ($assigneeId !== null && ! is_string($assigneeId)) {
            throw new UnexpectedValueException('Development task roadmap data requires a nullable string assignee value.');
        }

        $roadmapPositionIsInteger = $roadmapPosition === null
            || is_int($roadmapPosition)
            || (is_string($roadmapPosition) && ctype_digit($roadmapPosition));

        if (! $roadmapPositionIsInteger) {
            throw new UnexpectedValueException('Development task roadmap data requires a nullable integer roadmap position value.');
        }

        return new self(
            priority: TaskPriority::from($priority),
            assigneeId: $assigneeId,
            roadmapPosition: $roadmapPosition === null ? null : (int) $roadmapPosition,
        );
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public static function rules(): array
    {
        return [
            'priority' => ['required', Rule::enum(TaskPriority::class)],
            'assignee_id' => [
                'nullable',
                'uuid',
                Rule::exists(User::class, 'uuid')->where('is_active', true),
            ],
            'roadmap_position' => ['nullable', 'integer', 'min:1', 'max:9999'],
        ];
    }
}
