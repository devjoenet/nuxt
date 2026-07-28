<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Dto;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use UnexpectedValueException;

#[MapInputName(SnakeCaseMapper::class)]
final class RolePermissionsData extends Dto
{
    public function __construct(
        /** @var list<int> */
        public readonly array $permissionIds = [],
    ) {}

    /**
     * @param  array<string, mixed>  $attributes
     */
    public static function fromValidated(array $attributes): self
    {
        $permissionIds = $attributes['permission_ids'] ?? [];
        $attributes['permission_ids'] = is_array($permissionIds) ? array_values($permissionIds) : [];

        $data = self::factory()->ignoreMagicalMethod('fromValidated')->from($attributes);

        return $data instanceof self ? $data : throw new UnexpectedValueException('Role permissions data could not be created.');
    }
}
