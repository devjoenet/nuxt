<?php

declare(strict_types=1);

namespace App\Enums;

enum Permission: string
{
    case AdminDashboardView = 'admin.dashboard.view';
    case AdminUsersManage = 'admin.users.manage';
    case AdminRolesManage = 'admin.roles.manage';
    case AuditView = 'audit.view';
    case CrmView = 'crm.view';
    case CrmManage = 'crm.manage';
    case JobsView = 'jobs.view';
    case JobsManage = 'jobs.manage';
    case SettingsManage = 'settings.manage';
    case DevelopmentUserTestingView = 'development.user_testing.view';

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_map(static fn (self $permission): string => $permission->value, self::cases());
    }

    public function label(): string
    {
        return str($this->value)->replace('.', ' ')->title()->toString();
    }
}
