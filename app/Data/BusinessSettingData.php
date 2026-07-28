<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Dto;
use UnexpectedValueException;

final class BusinessSettingData extends Dto
{
    public function __construct(
        public readonly string $companyName,
        public readonly string $notificationEmail,
        public readonly int $defaultTaskDueDays,
    ) {}

    /** @param array<string, mixed> $attributes */
    public static function fromValidated(array $attributes): self
    {
        $companyName = $attributes['company_name'] ?? null;
        $notificationEmail = $attributes['notification_email'] ?? null;
        $defaultTaskDueDays = $attributes['default_task_due_days'] ?? null;

        if (! is_string($companyName) || ! is_string($notificationEmail)) {
            throw new UnexpectedValueException('Business setting data requires string company_name and notification_email values.');
        }

        if (is_int($defaultTaskDueDays)) {
            return new self($companyName, $notificationEmail, $defaultTaskDueDays);
        }

        if (is_string($defaultTaskDueDays) && ctype_digit($defaultTaskDueDays)) {
            return new self($companyName, $notificationEmail, (int) $defaultTaskDueDays);
        }

        throw new UnexpectedValueException('Business setting data requires an integer default_task_due_days value.');
    }
}
