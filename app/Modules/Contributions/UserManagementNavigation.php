<?php

declare(strict_types=1);

namespace App\Modules\Contributions;

use App\Modules\NavigationContribution;
use App\Modules\NavigationItem;
use App\Modules\WorkspaceUser;

final class UserManagementNavigation implements NavigationContribution
{
    /** @return list<NavigationItem> */
    public function items(WorkspaceUser $user): array
    {
        $items = [];
        foreach (['admin.users.manage' => ['users', 'Users', 'admin.users.index', 'Admin users · Invite a user'], 'admin.roles.manage' => ['roles', 'Roles', 'admin.roles.index', 'Roles and permissions · Choose a role']] as $permission => [$key, $label, $route, $description]) {
            if ($user->workspaceCan($permission)) {
                $items[] = new NavigationItem('admin.'.$key, $label, $route, 'admin', searchDescription: $description, order: $key === 'users' ? 10 : 20);
            }
        }

        return $items;
    }
}
