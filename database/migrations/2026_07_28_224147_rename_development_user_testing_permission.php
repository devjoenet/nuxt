<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        $this->renamePermission('development.user_testing.view', 'development.user_testing.manage');
    }

    public function down(): void
    {
        $this->renamePermission('development.user_testing.manage', 'development.user_testing.view');
    }

    private function renamePermission(string $from, string $to): void
    {
        $table = config('permission.table_names.permissions');
        $permission = DB::table($table)
            ->where('name', $from)
            ->where('guard_name', 'web')
            ->first(['id']);

        if ($permission !== null) {
            DB::table($table)
                ->where('id', $permission->id)
                ->update(['name' => $to, 'updated_at' => now()]);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
