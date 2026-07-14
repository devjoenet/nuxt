<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Override;

#[Fillable(['company_name', 'notification_email', 'default_task_due_days'])]
class BusinessSetting extends Model
{
    /** @use HasFactory<\Database\Factories\BusinessSettingFactory> */
    use HasFactory;

    public static function current(): self
    {
        return self::query()->firstOrCreate([], [
            'company_name' => config('app.name'),
            'notification_email' => config('services.leads.recipient'),
            'default_task_due_days' => 3,
        ]);
    }

    /** @return array<string, string> */
    #[Override]
    protected function casts(): array
    {
        return ['default_task_due_days' => 'integer'];
    }
}
