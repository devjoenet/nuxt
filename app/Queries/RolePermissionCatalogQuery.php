<?php

declare(strict_types=1);

namespace App\Queries;

use App\Enums\PermissionGroup;
use App\Modules\ModuleRegistry;
use Spatie\Permission\Models\Permission;

final class RolePermissionCatalogQuery
{
    public function __construct(private ModuleRegistry $registry) {}

    /** @return list<array{key: string, label: string, permissions: list<array{id: int, name: string, label: string}>}> */
    public function grouped(): array
    {
        $definitions = $this->registry->permissions();
        $stored = Permission::query()->where('guard_name', 'web')->orderBy('name')->get(['id', 'name'])->keyBy('name');
        $groups = [];
        // Registered permissions keep descriptor order; unknown persisted grants remain editable.
        $names = array_unique([...array_keys($definitions), ...$stored->keys()->all()]);
        foreach ($names as $name) {
            $permission = $stored->get($name);
            if ($permission === null) {
                continue;
            }

            $definition = $definitions[$name] ?? null;
            $group = $definition->group ?? 'other';
            $groups[$group] ??= ['key' => $group, 'label' => $definition->groupLabel ?? 'Other permissions', 'permissions' => []];
            $groups[$group]['permissions'][] = [
                'id' => (int) $permission->getKey(),
                'name' => $permission->name,
                'label' => $definition->label ?? str($permission->name)->replace(['.', '_'], ' ')->title()->toString(),
            ];
        }

        $ordered = [];
        foreach (PermissionGroup::cases() as $group) {
            if (isset($groups[$group->value])) {
                $ordered[$group->value] = $groups[$group->value];
            }
        }

        return array_values($ordered + $groups);
    }
}
