<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\AdminUserAccessData;
use App\Enums\RoleName;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Spatie\Activitylog\Facades\Activity;

class UpdateAdminUserAccess
{
    public function handle(User $user, AdminUserAccessData $data, User $actor): User
    {
        return DB::transaction(function () use ($user, $data, $actor): User {
            $user = User::query()->lockForUpdate()->findOrFail($user->id);

            $this->ensureActorCanAssignRole($data, $actor);
            $this->ensureUserCanBeChanged($user, $data, $actor);

            Activity::defaultCauser($actor, function () use ($user, $data, $actor): void {
                $user->update([
                    'is_active' => $data->isActive,
                    'deactivated_at' => $data->isActive ? null : now(),
                ]);
                $user->syncRoles([$data->role]);

                activity()
                    ->causedBy($actor)
                    ->performedOn($user)
                    ->withProperties(['role' => $data->role, 'is_active' => $data->isActive])
                    ->log('User access updated.');
            });

            return $user->refresh();
        });
    }

    private function ensureActorCanAssignRole(AdminUserAccessData $data, User $actor): void
    {
        if ($data->role === RoleName::SuperAdmin->value && ! $actor->hasRole(RoleName::SuperAdmin->value)) {
            throw ValidationException::withMessages(['role' => 'Only a Super Admin can assign the Super Admin role.']);
        }
    }

    private function ensureUserCanBeChanged(User $user, AdminUserAccessData $data, User $actor): void
    {
        if ($user->is($actor) && ! $data->isActive) {
            throw ValidationException::withMessages(['is_active' => 'You cannot deactivate your own account.']);
        }

        if ($user->hasRole(RoleName::SuperAdmin->value) && $data->role !== RoleName::SuperAdmin->value) {
            throw ValidationException::withMessages(['role' => 'The Super Admin role is protected.']);
        }
    }
}
