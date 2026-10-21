<?php

declare(strict_types=1);

namespace App\Modules;

interface NavigationContribution
{
    /** @return list<NavigationItem> */
    public function items(WorkspaceUser $user): array;
}
