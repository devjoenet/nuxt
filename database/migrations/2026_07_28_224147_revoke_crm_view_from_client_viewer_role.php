<?php

declare(strict_types=1);

use App\Enums\RoleName;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');
        $rolePivot = $columnNames['role_pivot_key'] ?? 'role_id';
        $permissionPivot = $columnNames['permission_pivot_key'] ?? 'permission_id';
        $roleId = DB::table($tableNames['roles'])
            ->where('name', RoleName::ClientViewer->value)
            ->where('guard_name', 'web')
            ->value('id');
        $permissionId = DB::table($tableNames['permissions'])
            ->where('name', 'crm.view')
            ->where('guard_name', 'web')
            ->value('id');

        if ($roleId !== null && $permissionId !== null) {
            DB::table($tableNames['role_has_permissions'])
                ->where($rolePivot, $roleId)
                ->where($permissionPivot, $permissionId)
                ->delete();
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        // The Client Viewer CRM grant is intentionally retired and must not be restored by rollback.
    }
};
