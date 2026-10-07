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
    case DevelopmentRequestsCreate = 'development.requests.create';
    case DevelopmentRequestsReview = 'development.requests.review';
    case DevelopmentRoadmapManage = 'development.roadmap.manage';
    case DevelopmentUserTestingManage = 'development.user_testing.manage';

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_map(static fn (self $permission): string => $permission->value, self::cases());
    }

    public function label(): string
    {
        return str($this->value)->replace('.', ' ')->title()->toString();
    }

    public function group(): PermissionGroup
    {
        return match ($this) {
            self::AdminDashboardView,
            self::AdminUsersManage,
            self::AdminRolesManage => PermissionGroup::Admin,
            self::AuditView => PermissionGroup::Audit,
            self::CrmView,
            self::CrmManage => PermissionGroup::Crm,
            self::DevelopmentRequestsCreate,
            self::DevelopmentRequestsReview,
            self::DevelopmentRoadmapManage,
            self::DevelopmentUserTestingManage => PermissionGroup::Development,
            self::JobsView,
            self::JobsManage => PermissionGroup::Jobs,
            self::SettingsManage => PermissionGroup::BusinessSettings,
        };
    }

    public function groupedLabel(): string
    {
        return str($this->value)
            ->after('.')
            ->replace(['.', '_'], ' ')
            ->title()
            ->toString();
    }
}
