<?php

declare(strict_types=1);

namespace App\Modules;

use Illuminate\Database\Eloquent\Model;

final readonly class ModuleManifest
{
    /**
     * @param  list<PermissionDefinition>  $permissions
     * @param  list<string>  $routeNames
     * @param  list<string>  $navigationKeys
     * @param  array<string, class-string<Model>>  $morphMap
     * @param  list<class-string<NavigationContribution>>  $navigation
     * @param  array<string, list<class-string>>  $extensions
     * @param  class-string<WorkspaceDashboardContribution>|null  $workspaceDashboard
     * @param  class-string<AdminSummaryContribution>|null  $adminSummary
     * @param  class-string<UserContextContribution>|null  $userContext
     * @param  list<string>  $frontendExports
     */
    public function __construct(
        public string $id,
        public array $permissions = [],
        public ?string $pageNamespace = null,
        public array $routeNames = [],
        public array $navigationKeys = [],
        public array $morphMap = [],
        public array $frontendExports = [],
        public array $navigation = [],
        public ?string $workspaceDashboard = null,
        public ?string $adminSummary = null,
        public ?string $userContext = null,
        public array $extensions = [],
        public int $manifestVersion = 1,
        public int $hostApiVersion = 1,
    ) {}
}
