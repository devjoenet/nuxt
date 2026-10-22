<?php

declare(strict_types=1);

namespace App\Support;

use App\Enums\RoleName;
use SoutheastCode\Modules\WorkspaceUser;

final class CrmAccess
{
    public static function administrator(?WorkspaceUser $user): bool
    {
        return $user?->workspaceHasAnyRole([RoleName::Admin->value, RoleName::SuperAdmin->value]) ?? false;
    }
}
