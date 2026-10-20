<?php

declare(strict_types=1);

namespace App\Modules;

final readonly class ModuleManifest
{
    /**
     * @param  list<PermissionDefinition>  $permissions
     * @param  list<string>  $routeNames
     * @param  list<string>  $navigationKeys
     * @param  array<string, class-string>  $morphMap
     * @param  list<string>  $frontendExports
     */
    public function __construct(
        public string $id,
        public array $permissions = [],
        public ?string $pageNamespace = null,
        public array $routeNames = [],
        public array $navigationKeys = [],
        public array $morphMap = [],
        public array $frontendExports = [],
        public int $manifestVersion = 1,
        public int $hostApiVersion = 1,
    ) {}
}
