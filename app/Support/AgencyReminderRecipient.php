<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\BusinessSetting;
use SoutheastCode\Crm\FollowUps\Contracts\ReminderRecipient;
use SoutheastCode\Crm\FollowUps\Models\FollowUpTask;

final class AgencyReminderRecipient implements ReminderRecipient
{
    public function email(FollowUpTask $task): ?string
    {
        return BusinessSetting::current()->notification_email;
    }
}
