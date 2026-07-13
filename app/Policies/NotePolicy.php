<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Note;
use App\Models\User;

class NotePolicy
{
    public function view(User $user, Note $note): bool
    {
        return $user->can('crm.view');
    }

    public function create(User $user): bool
    {
        return $user->can('crm.manage');
    }

    public function update(User $user, Note $note): bool
    {
        return $user->can('crm.manage');
    }

    public function delete(User $user, Note $note): bool
    {
        return $user->can('crm.manage');
    }
}
