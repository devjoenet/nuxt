<?php

declare(strict_types=1);

namespace App\Modules;

interface UserContextContribution
{
    /** @return array<string, mixed> */
    public function props(WorkspaceUser $user): array;
}
