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
        App\Models\Lead::class,
        App\Models\Contact::class,
        App\Models\Organization::class,
        App\Models\FollowUpTask::class,
        App\Models\CrmActivity::class,
        App\Models\Note::class,
        App\Models\DevelopmentTask::class,
        App\Models\DevelopmentTestRun::class,
    ]);
});
