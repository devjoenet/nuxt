<?php

declare(strict_types=1);

namespace App\Enums;

enum RoleName: string
{
    case SuperAdmin = 'Super Admin';
    case Admin = 'Admin';
    case SalesOps = 'Sales/Ops';
    case ClientViewer = 'Client Viewer';
}
