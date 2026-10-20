<?php

declare(strict_types=1);

namespace App\Providers;

use App\Enums\Permission;
use App\Enums\RoleName;
use App\Modules\ModuleManifest;
use App\Modules\ModuleRegistry;
use App\Modules\PermissionDefinition;
use Illuminate\Support\ServiceProvider;
use Override;

class ModuleServiceProvider extends ServiceProvider
{
    #[Override]
    public function register(): void
    {
        $this->app->singleton(ModuleRegistry::class);
    }

    public function boot(ModuleRegistry $registry): void
    {
        // In-place adapters: move each contribution with its owner during extraction.
        $groups = [];
        foreach (Permission::cases() as $permission) {
            $owner = match ($permission) {
                Permission::AdminUsersManage, Permission::AdminRolesManage => 'users',
                Permission::AuditView => 'audit',
                Permission::CrmView, Permission::CrmManage => 'crm',
                Permission::DevelopmentRequestsCreate, Permission::DevelopmentRequestsReview,
                Permission::DevelopmentRoadmapManage, Permission::DevelopmentUserTestingManage => 'dev',
                default => 'foundation',
            };
            $roles = [RoleName::SuperAdmin->value, RoleName::Admin->value];
            if (in_array($permission, [Permission::AdminDashboardView, Permission::CrmView, Permission::CrmManage, Permission::JobsView], true)) {
                $roles[] = RoleName::SalesOps->value;
            }

            $groups[$owner][] = new PermissionDefinition(
                $permission->value, $permission->groupedLabel(),
                $permission->group()->value, $permission->group()->label(), $roles,
            );
        }

        foreach ($groups as $owner => $permissions) {
            $registry->register(new ModuleManifest($owner, $permissions));
        }

        $this->app->booted(fn () => $registry->freeze());
    }
}
