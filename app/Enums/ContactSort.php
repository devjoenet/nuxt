<?php

declare(strict_types=1);

namespace App\Enums;

enum ContactSort: string
{
    case Name = 'name';
    case CreatedAt = 'created_at';
}
