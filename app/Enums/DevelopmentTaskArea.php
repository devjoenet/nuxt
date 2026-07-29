<?php

declare(strict_types=1);

namespace App\Enums;

enum DevelopmentTaskArea: string
{
    case MarketingSite = 'marketing_site';
    case Crm = 'crm';
    case Admin = 'admin';
    case Development = 'development';
    case Authentication = 'authentication';
    case Operations = 'operations';
    case Infrastructure = 'infrastructure';
    case Other = 'other';

    public function label(): string
    {
        return str($this->value)->replace('_', ' ')->title()->toString();
    }
}
