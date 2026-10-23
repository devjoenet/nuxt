<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\DashboardController;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Resources\UserResource;
use SoutheastCode\Audit\Http\Controllers\RecordHistoryController;
use SoutheastCode\Audit\Queries\RecentAdminChangesQuery;
use SoutheastCode\Modules\WorkspaceComposition;

test('host composition cannot import optional domain persistence', function (): void {
    expect([
        AdminDashboardController::class,
        DashboardController::class,
        RecordHistoryController::class,
        HandleInertiaRequests::class,
        UserResource::class,
        WorkspaceComposition::class,
        RecentAdminChangesQuery::class,
    ])->not->toUse([
        SoutheastCode\Crm\Leads\Models\Lead::class,
        SoutheastCode\Crm\Contacts\Models\Contact::class,
        SoutheastCode\Crm\Organizations\Models\Organization::class,
        SoutheastCode\Crm\FollowUps\Models\FollowUpTask::class,
        SoutheastCode\Crm\Shared\Models\CrmActivity::class,
        SoutheastCode\Crm\Shared\Models\Note::class,
        SoutheastCode\DevelopmentWorkflow\Models\DevelopmentTask::class,
        SoutheastCode\DevelopmentWorkflow\Models\DevelopmentTestRun::class,
    ]);
});
