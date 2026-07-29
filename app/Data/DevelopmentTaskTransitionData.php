<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\DevelopmentTaskStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Dto;
use UnexpectedValueException;

final class DevelopmentTaskTransitionData extends Dto
{
    public function __construct(public readonly DevelopmentTaskStatus $status) {}

    /** @param array<string, mixed> $attributes */
    public static function fromRaw(array $attributes): self
    {
        return self::fromValidated(validator($attributes, self::rules())->validate());
    }

    /** @param array<mixed> $attributes */
    public static function fromValidated(array $attributes): self
    {
        $status = $attributes['status'] ?? null;

        if (! is_string($status)) {
            throw new UnexpectedValueException('Development task transition data requires a string status value.');
        }

        return new self(DevelopmentTaskStatus::from($status));
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public static function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(DevelopmentTaskStatus::class)],
        ];
    }
}
