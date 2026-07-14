<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\BusinessSetting;
use App\Models\User;

class BusinessSettingPolicy
{
    public function view(User $user, BusinessSetting $businessSetting): bool
    {
        return $user->can('settings.manage');
    }

    public function update(User $user, BusinessSetting $businessSetting): bool
    {
        return $user->can('settings.manage');
    }
}
