<?php

declare(strict_types=1);

namespace App\Queries;

use App\Enums\Permission as PermissionName;
use App\Enums\PermissionGroup;
use Spatie\Permission\Models\Permission;

final class RolePermissionCatalogQuery
{
    /**
     * @return array<int, array{
     *     key: string,
     *     label: string,
     *     permissions: array<int, array{id: int, name: string, label: string}>
     * }>
     */
    public function grouped(): array
    {
        $permissionsByGroup = Permission::query()
            ->where('guard_name', 'web')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->groupBy(fn (Permission $permission): string => $this->group($permission)->value);

        return collect(PermissionGroup::cases())
            ->map(function (PermissionGroup $group) use ($permissionsByGroup): ?array {
                $permissions = $permissionsByGroup
                    ->get($group->value, collect())
                    ->sortBy(fn (Permission $permission): int => $this->order($permission))
                    ->map(fn (Permission $permission): array => [
                        'id' => (int) $permission->getKey(),
                        'name' => $permission->name,
                        'label' => $this->label($permission),
                    ])
                    ->values()
                    ->all();

                if ($permissions === []) {
                    return null;
                }

                return [
                    'key' => $group->value,
                    'label' => $group->label(),
                    'permissions' => $permissions,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    private function group(Permission $permission): PermissionGroup
    {
        return PermissionName::tryFrom($permission->name)?->group() ?? PermissionGroup::Other;
    }

    private function order(Permission $permission): int
    {
        $position = array_search(PermissionName::tryFrom($permission->name), PermissionName::cases(), true);

        return $position === false ? PHP_INT_MAX : $position;
    }

    private function label(Permission $permission): string
    {
        return PermissionName::tryFrom($permission->name)?->groupedLabel()
            ?? str($permission->name)->replace(['.', '_'], ' ')->title()->toString();
    }
}
