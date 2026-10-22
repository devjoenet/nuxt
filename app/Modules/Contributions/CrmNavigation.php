<?php

declare(strict_types=1);

namespace App\Modules\Contributions;

use SoutheastCode\Modules\NavigationContribution;
use SoutheastCode\Modules\NavigationItem;
use SoutheastCode\Modules\WorkspaceUser;

final class CrmNavigation implements NavigationContribution
{
    /** @return list<NavigationItem> */
    public function items(WorkspaceUser $user): array
    {
        if (! $user->workspaceCan('crm.view')) {
            return [];
        }

        return [
            new NavigationItem('crm.leads', 'Leads', 'crm.leads.index', 'crm', searchDescription: 'CRM leads'),
        ];
    }
}
