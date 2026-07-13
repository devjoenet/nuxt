<?php

declare(strict_types=1);

namespace App\Enums;

enum LeadSource: string
{
    case Website = 'website';
    case Referral = 'referral';
    case Direct = 'direct';
    case Partner = 'partner';
}
