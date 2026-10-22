<?php

declare(strict_types=1);

namespace App\Providers;

use App\Audit\Presenters\CrmActivityPresenter;
use App\Audit\Presenters\DevelopmentActivityPresenter;
use App\Audit\Presenters\FoundationActivityPresenter;
use App\Enums\Permission;
use App\Enums\RoleName;
use App\Models\BusinessSetting;
use App\Models\Contact;
use App\Models\CrmActivity;
use App\Models\DevelopmentTask;
use App\Models\DevelopmentTaskLink;
use App\Models\DevelopmentTaskNote;
use App\Models\DevelopmentTestRun;
use App\Models\FollowUpTask;
use App\Models\Lead;
use App\Models\Note;
use App\Models\Organization;
use App\Models\UserTestingPage;
use App\Modules\Contributions\CrmNavigation;
use App\Modules\Contributions\DevelopmentNavigation;
use App\Modules\Contributions\FoundationNavigation;
use App\Queries\CrmAdminSummaryQuery;
use App\Queries\CrmWorkspaceDashboardQuery;
use App\Queries\PendingDevelopmentTestsQuery;
use App\Support\WorkspaceUsers;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use LogicException;
use SoutheastCode\Modules\ModuleManifest;
use SoutheastCode\Modules\ModuleRegistry;
use SoutheastCode\Modules\PermissionDefinition;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\PermissionRegistrar;

class ModuleServiceProvider extends ServiceProvider
{
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
            if (in_array($owner, ['audit', 'users'], true)) {
                continue;
            }

            $roles = [RoleName::SuperAdmin->value, RoleName::Admin->value];
            if (in_array($permission, [Permission::AdminDashboardView, Permission::CrmView, Permission::CrmManage, Permission::JobsView], true)) {
                $roles[] = RoleName::SalesOps->value;
            }

            $groups[$owner][] = new PermissionDefinition(
                $permission->value, $permission->groupedLabel(),
                $permission->group()->value, $permission->group()->label(), $roles,
            );
        }

        $maps = [
            'foundation' => [
                'user' => WorkspaceUsers::model(),
                'business-setting' => BusinessSetting::class,
                'role' => $this->morphModel(app(PermissionRegistrar::class)->getRoleClass()),
                'permission' => $this->morphModel(app(PermissionRegistrar::class)->getPermissionClass()),
                'audit-activity' => $this->morphModel(config('activitylog.activity_model', Activity::class)),
            ],
            'crm' => [
                'crm-organization' => Organization::class,
                'crm-contact' => Contact::class,
                'crm-lead' => Lead::class,
                'crm-follow-up-task' => FollowUpTask::class,
                'crm-note' => Note::class,
                'crm-activity' => CrmActivity::class,
            ],
            'dev' => [
                'development-task' => DevelopmentTask::class,
                'development-task-link' => DevelopmentTaskLink::class,
                'development-task-note' => DevelopmentTaskNote::class,
                'development-test-run' => DevelopmentTestRun::class,
                'user-testing-page' => UserTestingPage::class,
            ],
        ];

        foreach ($groups as $owner => $permissions) {
            if (! in_array($owner, config('modules.enabled', ['foundation', 'users', 'audit', 'crm', 'dev']), true)) {
                continue;
            }

            $navigation = match ($owner) {
                'foundation' => FoundationNavigation::class,
                'crm' => CrmNavigation::class,
                'dev' => DevelopmentNavigation::class,
            };
            $presenters = match ($owner) {
                'foundation' => [FoundationActivityPresenter::class],
                'crm' => [CrmActivityPresenter::class],
                'dev' => [DevelopmentActivityPresenter::class],
            };
            $registry->register(new ModuleManifest($owner, $permissions, morphMap: $maps[$owner],
                navigation: [$navigation],
                workspaceDashboard: $owner === 'crm' ? CrmWorkspaceDashboardQuery::class : null,
                adminSummary: $owner === 'crm' ? CrmAdminSummaryQuery::class : null,
                userContext: $owner === 'dev' ? PendingDevelopmentTestsQuery::class : null,
                extensions: ['audit.presenters' => $presenters, 'audit.admin-presenters' => $owner === 'dev' ? [] : $presenters],
            ));
        }
    }

    /** @return class-string<Model> */
    private function morphModel(mixed $class): string
    {
        if (! is_string($class) || ! is_subclass_of($class, Model::class)) {
            throw new LogicException('Configured morph models must extend Eloquent Model.');
        }

        return $class;
    }
}
