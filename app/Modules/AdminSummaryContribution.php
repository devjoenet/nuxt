<?php

declare(strict_types=1);

namespace App\Modules;

interface AdminSummaryContribution
{
    /** @return list<array{label: string, value: ?int, url: string, attention: bool}> */
    public function stats(WorkspaceUser $user): array;
}
