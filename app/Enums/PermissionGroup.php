<?php

declare(strict_types=1);

namespace App\Enums;

enum PermissionGroup: string
{
    case Admin = 'admin';
    case Audit = 'audit';
    case Crm = 'crm';
    case Development = 'development';
    case Jobs = 'jobs';
    case BusinessSettings = 'business_settings';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Admin',
            self::Audit => 'Audit log',
            self::Crm => 'CRM',
            self::Development => 'Development',
            self::Jobs => 'Jobs & queues',
            self::BusinessSettings => 'Business settings',
            self::Other => 'Other permissions',
        };
    }
}
