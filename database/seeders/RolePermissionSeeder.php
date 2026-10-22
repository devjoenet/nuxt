<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\RoleName;
use Illuminate\Database\Seeder;
use SoutheastCode\Modules\ModuleRegistry;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function __construct(private ModuleRegistry $registry) {}

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        foreach (RoleName::cases() as $role) {
            Role::findOrCreate($role->value, 'web');
        }

        foreach ($this->registry->permissions() as $definition) {
            $permission = Permission::findOrCreate($definition->key, 'web');
            foreach ($definition->defaultRoles as $role) {
                Role::findOrCreate($role, 'web')->givePermissionTo($permission);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
