<?php

declare(strict_types=1);

namespace App\Modules;

use LogicException;

final class ModuleRegistry
{
    /** @var array<string, ModuleManifest> */
    private array $modules = [];

    private bool $frozen = false;

    public function register(ModuleManifest $module): void
    {
        if ($this->frozen) {
            throw new LogicException('Module registration is closed after application boot.');
        }

        if ($module->id === '' || isset($this->modules[$module->id])) {
            throw new LogicException("Duplicate or empty module identifier: {$module->id}");
        }

        if ($module->manifestVersion !== 1 || $module->hostApiVersion !== 1) {
            throw new LogicException("Unsupported manifest or host API version for {$module->id}.");
        }

        $candidate = [...$this->modules, $module->id => $module];
        $claims = [];
        $groups = [];
        foreach ($candidate as $manifest) {
            foreach ($manifest->permissions as $permission) {
                $this->claim($claims, 'permission', $permission->key, $manifest->id);
                if (isset($groups[$permission->group]) && $groups[$permission->group] !== $permission->groupLabel) {
                    throw new LogicException("Conflicting permission group label: {$permission->group}");
                }

                $groups[$permission->group] = $permission->groupLabel;
            }

            if ($manifest->pageNamespace !== null) {
                $this->claim($claims, 'page namespace', $manifest->pageNamespace, $manifest->id);
            }

            foreach (['routeNames' => 'route', 'navigationKeys' => 'navigation', 'frontendExports' => 'frontend export'] as $property => $kind) {
                foreach ($manifest->{$property} as $key) {
                    $this->claim($claims, $kind, $key, $manifest->id);
                }
            }

            foreach ($manifest->morphMap as $alias => $class) {
                $this->claim($claims, 'morph alias', $alias, $manifest->id);
                if (! class_exists($class)) {
                    throw new LogicException("Missing morph model {$class} in {$manifest->id}.");
                }
            }
        }

        $this->modules = $candidate;
    }

    public function freeze(): void
    {
        $this->frozen = true;
    }

    public function has(string $id): bool
    {
        return isset($this->modules[$id]);
    }

    /** @return array<string, ModuleManifest> */
    public function all(): array
    {
        return $this->modules;
    }

    /** @return array<string, PermissionDefinition> */
    public function permissions(): array
    {
        $permissions = [];
        foreach ($this->modules as $module) {
            foreach ($module->permissions as $permission) {
                $permissions[$permission->key] = $permission;
            }
        }

        return $permissions;
    }

    /** @param array<string, string> $claims */
    private function claim(array &$claims, string $kind, string $key, string $owner): void
    {
        $claim = "{$kind}:{$key}";
        if ($key === '' || isset($claims[$claim])) {
            $previous = $claims[$claim] ?? 'empty key';
            throw new LogicException("Conflicting {$kind} {$key}: {$previous} and {$owner}.");
        }

        $claims[$claim] = $owner;
    }
}
