<?php

declare(strict_types=1);

namespace App\Data;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Dto;
use UnexpectedValueException;

final class RequestDevelopmentTaskTestingData extends Dto
{
    public function __construct(
        public readonly string $testerId,
        public readonly string $instructions,
        public readonly ?string $buildReference,
    ) {}

    /** @param array<string, mixed> $attributes */
    public static function fromRaw(array $attributes): self
    {
        return self::fromValidated(validator($attributes, self::rules())->validate());
    }

    /** @param array<mixed> $attributes */
    public static function fromValidated(array $attributes): self
    {
        $testerId = $attributes['tester_id'] ?? null;
        $instructions = $attributes['instructions'] ?? null;
        $buildReference = $attributes['build_reference'] ?? null;

        if (! is_string($testerId) || ! is_string($instructions)) {
            throw new UnexpectedValueException('Development task testing data requires string tester and instruction values.');
        }

        if ($buildReference !== null && ! is_string($buildReference)) {
            throw new UnexpectedValueException('Development task testing data requires a nullable string build reference.');
        }

        $buildReference = $buildReference === null ? null : mb_trim($buildReference);

        return new self(
            testerId: $testerId,
            instructions: mb_trim($instructions),
            buildReference: $buildReference === '' ? null : $buildReference,
        );
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public static function rules(): array
    {
        return [
            'tester_id' => [
                'required',
                'uuid',
                Rule::exists(User::class, 'uuid')->where('is_active', true),
            ],
            'instructions' => ['required', 'string', 'max:20000'],
            'build_reference' => ['nullable', 'string', 'max:2048'],
        ];
    }
}
