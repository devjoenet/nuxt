<?php

declare(strict_types=1);

namespace App\Modules\Contributions;

use App\Modules\NavigationContribution;
use App\Modules\NavigationItem;
use App\Modules\WorkspaceUser;

final class AuditNavigation implements NavigationContribution
{
    /** @return list<NavigationItem> */
    public function items(WorkspaceUser $user): array
    {
        return $user->workspaceCan('audit.view') ? [new NavigationItem('admin.audit', 'Audit log', 'admin.audit.index', 'admin', searchDescription: 'Activity history · Sensitive workspace changes', order: 30)] : [];
    }
}
