<?php

declare(strict_types=1);

namespace App\Queries;

use App\Enums\DevelopmentTestRunStatus;
use App\Models\DevelopmentTestRun;
use App\Modules\UserContextContribution;
use App\Modules\WorkspaceUser;

final class PendingDevelopmentTestsQuery implements UserContextContribution
{
    /** @return array{has_pending_development_tests: bool} */
    public function props(WorkspaceUser $user): array
    {
        return ['has_pending_development_tests' => once(fn (): bool => DevelopmentTestRun::query()
            ->where('tester_id', $user->workspaceId())
            ->where('status', DevelopmentTestRunStatus::Pending)->exists())];
    }
}
