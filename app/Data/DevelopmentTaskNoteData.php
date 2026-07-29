<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\DevelopmentNoteAudience;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Dto;
use UnexpectedValueException;

final class DevelopmentTaskNoteData extends Dto
{
    public function __construct(
        public readonly string $body,
        public readonly DevelopmentNoteAudience $audience,
    ) {}

    /** @param array<mixed> $attributes */
    public static function fromValidated(array $attributes): self
    {
        $body = $attributes['body'] ?? null;
        $audience = $attributes['audience'] ?? null;

        if (! is_string($body) || ! is_string($audience)) {
            throw new UnexpectedValueException('Development task note data requires string body and audience values.');
        }

        return new self(
            body: mb_trim($body),
            audience: DevelopmentNoteAudience::from($audience),
        );
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public static function rules(): array
    {
        return [
            'body' => ['required', 'string', 'max:10000'],
            'audience' => ['required', Rule::enum(DevelopmentNoteAudience::class)],
        ];
    }
}
