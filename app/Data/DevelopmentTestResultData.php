<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\DevelopmentTestRunStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Dto;
use UnexpectedValueException;

final class DevelopmentTestResultData extends Dto
{
    public function __construct(
        public readonly DevelopmentTestRunStatus $status,
        public readonly ?string $feedback,
    ) {}

    /** @param array<string, mixed> $attributes */
    public static function fromRaw(array $attributes): self
    {
        return self::fromValidated(validator($attributes, self::rules())->validate());
    }

    /** @param array<mixed> $attributes */
    public static function fromValidated(array $attributes): self
    {
        $status = $attributes['status'] ?? null;
        $feedback = $attributes['feedback'] ?? null;

        if (! is_string($status)) {
            throw new UnexpectedValueException('Development test result data requires a string status value.');
        }

        if ($feedback !== null && ! is_string($feedback)) {
            throw new UnexpectedValueException('Development test result data requires nullable string feedback.');
        }

        $feedback = $feedback === null ? null : mb_trim($feedback);

        return new self(
            status: DevelopmentTestRunStatus::from($status),
            feedback: $feedback === '' ? null : $feedback,
        );
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public static function rules(): array
    {
        return [
            'status' => [
                'required',
                Rule::enum(DevelopmentTestRunStatus::class)->only([
                    DevelopmentTestRunStatus::Accepted,
                    DevelopmentTestRunStatus::ChangesRequested,
                ]),
            ],
            'feedback' => [
                'nullable',
                'string',
                'max:20000',
                'required_if:status,'.DevelopmentTestRunStatus::ChangesRequested->value,
            ],
        ];
    }
}
