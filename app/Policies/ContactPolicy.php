<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Contact;
use App\Models\User;

class ContactPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('crm.view');
    }

    public function view(User $user, Contact $contact): bool
    {
        return $user->can('crm.view') && $contact->isVisibleTo($user);
    }

    public function create(User $user): bool
    {
        return $user->can('crm.manage');
    }

    public function update(User $user, Contact $contact): bool
    {
        return $user->can('crm.manage') && $contact->isVisibleTo($user);
    }

    public function delete(User $user, Contact $contact): bool
    {
        return $user->can('crm.manage') && $contact->isVisibleTo($user);
    }

    public function archive(User $user, Contact $contact): bool
    {
        return $user->can('crm.manage') && $contact->isVisibleTo($user);
    }

    public function restore(User $user, Contact $contact): bool
    {
        return $user->can('crm.manage') && $contact->isVisibleTo($user);
    }

    public function assign(User $user, Contact $contact): bool
    {
        return $user->can('crm.manage') && $user->isCrmAdministrator() && $contact->isVisibleTo($user);
    }
}
