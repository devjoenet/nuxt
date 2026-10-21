<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use App\Models\User;

class AlternateWorkspaceUser extends User
{
    protected $table = 'users';
}
