<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\DevelopmentTaskLinkType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Dto;
use UnexpectedValueException;

final class DevelopmentTaskLinkData extends Dto
{
    public function __construct(
        public readonly DevelopmentTaskLinkType $type,
        public readonly ?string $label,
        public readonly string $url,
    ) {}

    /** @param array<string, mixed> $attributes */
    public static function fromRaw(array $attributes): self
    {
        return self::fromValidated(validator($attributes, self::rules())->validate());
    }

    /** @param array<mixed> $attributes */
    public static function fromValidated(array $attributes): self
    {
        $type = $attributes['type'] ?? null;
        $label = $attributes['label'] ?? null;
        $url = $attributes['url'] ?? null;

        if (! is_string($type) || ! is_string($url)) {
            throw new UnexpectedValueException('Development task link data requires string type and URL values.');
        }

        if ($label !== null && ! is_string($label)) {
            throw new UnexpectedValueException('Development task link data requires a nullable string label value.');
        }

        return new self(
            type: DevelopmentTaskLinkType::from($type),
            label: $label,
            url: $url,
        );
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public static function rules(): array
    {
        return [
            'type' => ['required', Rule::enum(DevelopmentTaskLinkType::class)],
            'label' => ['nullable', 'string', 'max:255'],
            'url' => ['required', 'url:http,https', 'max:2048'],
        ];
    }
}
