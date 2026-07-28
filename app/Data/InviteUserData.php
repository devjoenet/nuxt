<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Dto;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use UnexpectedValueException;

#[MapInputName(SnakeCaseMapper::class)]
final class InviteUserData extends Dto
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly string $role,
    ) {}

    /**
     * @param  array<string, mixed>  $attributes
     */
    public static function fromValidated(array $attributes): self
    {
        $data = self::factory()->ignoreMagicalMethod('fromValidated')->from($attributes);

        return $data instanceof self ? $data : throw new UnexpectedValueException('Invite user data could not be created.');
    }
}
