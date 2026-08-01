<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Dto;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use UnexpectedValueException;

#[MapInputName(SnakeCaseMapper::class)]
final class CreateRoleData extends Dto
{
    public function __construct(
        public readonly string $name,
        /** @var list<int> */
        public readonly array $permissionIds = [],
    ) {}

    /** @param array<string, mixed> $attributes */
    public static function fromValidated(array $attributes): self
    {
        $permissionIds = $attributes['permission_ids'] ?? [];
        $attributes['permission_ids'] = is_array($permissionIds) ? array_values($permissionIds) : [];

        $data = self::factory()->ignoreMagicalMethod('fromValidated')->from($attributes);

        return $data instanceof self ? $data : throw new UnexpectedValueException('Create role data could not be created.');
    }
}
