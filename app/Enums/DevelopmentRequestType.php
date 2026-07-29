<?php

declare(strict_types=1);

namespace App\Enums;

enum DevelopmentRequestType: string
{
    case Bug = 'bug';
    case Feature = 'feature';
    case Improvement = 'improvement';
    case Maintenance = 'maintenance';
    case TechnicalDebt = 'technical_debt';
    case Other = 'other';

    public function label(): string
    {
        return str($this->value)->replace('_', ' ')->title()->toString();
    }
}
