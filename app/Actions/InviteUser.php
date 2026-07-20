<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\InviteUserData;
use App\Enums\RoleName;
use App\Models\User;
use App\Notifications\UserInvitationNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class InviteUser
{
    public function handle(InviteUserData $data, User $actor): User
    {
        if ($data->role === RoleName::SuperAdmin->value && ! $actor->hasRole(RoleName::SuperAdmin->value)) {
            throw ValidationException::withMessages(['role' => 'Only a Super Admin can assign the Super Admin role.']);
        }

        /** @var array{0: User, 1: string} $invitation */
        $invitation = DB::transaction(function () use ($data): array {
            $user = User::query()->create([
                'name' => $data->name,
                'email' => $data->email,
                'password' => Str::password(64),
            ]);
            $user->syncRoles([$data->role]);

            $token = Password::broker(config()->string('fortify.passwords', 'users'))->createToken($user);

            return [$user, $token];
        });

        [$user, $token] = $invitation;

        $user->notify(new UserInvitationNotification($token));

        activity()
            ->causedBy($actor)
            ->performedOn($user)
            ->withProperties(['role' => $data->role])
            ->log('User invitation sent.');

        return $user;
    }
}
