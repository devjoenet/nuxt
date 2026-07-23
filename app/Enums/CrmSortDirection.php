<?php

declare(strict_types=1);

namespace App\Enums;

enum CrmSortDirection: string
{
    case Asc = 'asc';
    case Desc = 'desc';
}
