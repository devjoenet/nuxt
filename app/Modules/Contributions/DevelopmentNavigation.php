<?php

declare(strict_types=1);

namespace App\Modules\Contributions;

use App\Queries\PendingDevelopmentTestsQuery;
use SoutheastCode\Modules\NavigationContribution;
use SoutheastCode\Modules\NavigationItem;
use SoutheastCode\Modules\WorkspaceUser;

final class DevelopmentNavigation implements NavigationContribution
{
    /** @return list<NavigationItem> */
    public function items(WorkspaceUser $user): array
    {
        $pending = app(PendingDevelopmentTestsQuery::class)->props($user)['has_pending_development_tests'];
        $items = [];
        if (! $pending && ! collect(['development.requests.create', 'development.requests.review', 'development.roadmap.manage', 'development.user_testing.manage'])->contains(fn (string $permission): bool => $user->workspaceCan($permission))) {
            return [];
        }

        $items[] = new NavigationItem('development', 'Development', 'development.index', icon: 'i-ph-code', order: 20);
        $items[] = new NavigationItem('development.overview', 'Overview', 'development.index', 'development', searchDescription: 'Development workspace · Roadmap work', exact: true);
        foreach ([['development.roadmap.manage', 'roadmap', 'Roadmap', 'development.roadmap.index', 'Development roadmap · Planned active and blocked work'], ['development.user_testing.manage', 'testing', 'User testing', 'development.userTesting.index', 'Task-specific testing · Testing queue'], ['development.requests.review', 'review', 'Review queue', 'development.review.index', 'Development review queue'], ['development.requests.create', 'requests', 'My requests', 'development.requests.index', 'Development requests · Active requests'], ['development.requests.create', 'create', 'Submit request', 'development.requests.create', 'Submit a development request · Describe the problem']] as [$permission, $key, $label, $route, $description]) {
            if ($user->workspaceCan($permission) || ($key === 'testing' && $pending)) {
                $items[] = new NavigationItem('development.'.$key, $label, $route, 'development', searchDescription: $description, exact: $key === 'requests');
            }
        }

        return $items;
    }
}
