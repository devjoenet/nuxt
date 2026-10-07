<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        Permission::query()
            ->where('guard_name', 'web')
            ->where('name', 'like', '%.gallery.view')
            ->get()
            ->each(fn (Permission $permission): ?bool => $permission->delete());

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
