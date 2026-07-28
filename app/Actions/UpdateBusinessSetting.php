<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\BusinessSettingData;
use App\Models\BusinessSetting;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UpdateBusinessSetting
{
    public function handle(BusinessSettingData $data, User $actor): BusinessSetting
    {
        return DB::transaction(function () use ($data, $actor): BusinessSetting {
            $setting = BusinessSetting::current();
            $setting = BusinessSetting::query()->lockForUpdate()->findOrFail($setting->id);

            $setting->update([
                'company_name' => $data->companyName,
                'notification_email' => $data->notificationEmail,
                'default_task_due_days' => $data->defaultTaskDueDays,
            ]);

            $changedFields = array_values(array_diff(array_keys($setting->getChanges()), ['updated_at']));

            if ($changedFields !== []) {
                activity()
                    ->useLog('settings')
                    ->causedBy($actor)
                    ->performedOn($setting)
                    ->withProperties(['changed_fields' => $changedFields])
                    ->log('Business settings updated.');
            }

            return $setting->refresh();
        });
    }
}
