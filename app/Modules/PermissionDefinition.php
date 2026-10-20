<?php

declare(strict_types=1);

namespace App\Modules;

final readonly class PermissionDefinition
{
    /** @param list<string> $defaultRoles */
    public function __construct(
        public string $key,
        public string $label,
        public string $group,
        public string $groupLabel,
        public array $defaultRoles = [],
    ) {}
}
