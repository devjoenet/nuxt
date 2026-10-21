<?php

declare(strict_types=1);

namespace App\Modules\Contributions;

use App\Modules\NavigationContribution;
use App\Modules\NavigationItem;
use App\Modules\WorkspaceUser;

final class FoundationNavigation implements NavigationContribution
{
    /** @return list<NavigationItem> */
    public function items(WorkspaceUser $user): array
    {
        $items = [new NavigationItem('home', 'Home', 'dashboard', icon: 'i-ph-house', searchDescription: 'Workspace home')];
        if ($user->workspaceCan('admin.dashboard.view')) {
            $items[] = new NavigationItem('admin', 'Admin', 'admin.dashboard', icon: 'i-ph-shield-check', exact: true, order: 30);
            $items[] = new NavigationItem('admin.overview', 'Overview', 'admin.dashboard', 'admin', searchDescription: 'Admin dashboard · Recent activity · Recent users', exact: true);
            foreach (['settings.manage' => ['settings', 'Business settings', 'admin.settings.edit', 'Workspace defaults'], 'jobs.view' => ['jobs', 'Jobs & queues', 'admin.jobs.index', 'Jobs and queues · Queue health · Horizon monitoring']] as $permission => [$key, $label, $route, $description]) {
                if ($user->workspaceCan($permission)) {
                    $items[] = new NavigationItem('admin.'.$key, $label, $route, 'admin', searchDescription: $description, order: $key === 'settings' ? 40 : 50);
                }
            }
        }

        $items[] = new NavigationItem('settings', 'Settings', 'profile.edit', icon: 'i-ph-gear-six', order: 40);
        $items[] = new NavigationItem('profile', 'Profile', 'profile.edit', 'settings', searchDescription: 'Profile settings', exact: true);
        $items[] = new NavigationItem('security', 'Security', 'security.show', 'settings', searchDescription: 'Security settings · Password · Two-factor authentication · Account');

        return $items;
    }
}
