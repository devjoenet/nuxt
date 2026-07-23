<?php

declare(strict_types=1);

namespace App\Enums;

enum LeadSort: string
{
    case CreatedAt = 'created_at';
    case Status = 'status';
}
