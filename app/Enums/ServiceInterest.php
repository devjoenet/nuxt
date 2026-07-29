<?php

declare(strict_types=1);

namespace App\Enums;

enum ServiceInterest: string
{
    case OperationsApplication = 'operations-app';
    case DashboardPortal = 'dashboard-portal';
    case SystemIntegration = 'integration';
    case ReportingVisibility = 'reporting';
    case LegacyModernization = 'modernization';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::OperationsApplication => 'Operations application',
            self::DashboardPortal => 'Dashboard or portal',
            self::SystemIntegration => 'System integration',
            self::ReportingVisibility => 'Reporting and visibility',
            self::LegacyModernization => 'Legacy modernization',
            self::Other => 'Other',
        };
    }

    /** @return array<int, array{label: string, value: string}> */
    public static function options(): array
    {
        return array_map(fn (self $interest): array => [
            'label' => $interest->label(),
            'value' => $interest->value,
        ], self::cases());
    }
}
