<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\BusinessSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<BusinessSetting> */
class BusinessSettingFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'company_name' => 'Southeast Code',
            'notification_email' => 'hello@example.com',
            'default_task_due_days' => 3,
        ];
    }
}
