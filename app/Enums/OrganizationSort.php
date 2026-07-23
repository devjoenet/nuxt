<?php

declare(strict_types=1);

namespace App\Enums;

enum OrganizationSort: string
{
    case Name = 'name';
    case CreatedAt = 'created_at';
}
