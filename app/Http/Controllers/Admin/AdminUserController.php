<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\RoleName;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAdminUserRequest;
use App\Http\Requests\Admin\UpdateAdminUserRequest;
use App\Http\Resources\AdminUserResource;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class AdminUserController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', User::class);

        return Inertia::render('Admin/Users/Index', [
            'users' => AdminUserResource::collection(User::query()->with('roles')->latest()->paginate(20)->withQueryString()),
            'roles' => Role::query()->orderBy('name')->get(['name'])->map(fn (Role $role): array => [
                'label' => $role->name,
                'value' => $role->name,
            ]),
        ]);
    }

    public function store(StoreAdminUserRequest $request): RedirectResponse
    {
        $this->authorize('create', User::class);
        $currentUser = $this->authenticatedUser($request);

        $attributes = $request->validated();

        if ($attributes['role'] === RoleName::SuperAdmin->value && ! $currentUser->hasRole(RoleName::SuperAdmin->value)) {
            throw ValidationException::withMessages(['role' => 'Only a Super Admin can assign the Super Admin role.']);
        }

        $user = User::query()->create([
            'name' => $attributes['name'],
            'email' => $attributes['email'],
            'password' => Str::password(64),
        ]);
        $user->assignRole($attributes['role']);
        $user->sendPasswordResetNotification(Password::broker()->createToken($user));

        activity()
            ->performedOn($user)
            ->withProperties(['role' => $attributes['role']])
            ->log('User invitation sent.');

        return to_route('admin.users.index')->with('success', 'Invitation sent.');
    }

    public function update(UpdateAdminUserRequest $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);
        $currentUser = $this->authenticatedUser($request);

        $attributes = $request->validated();

        if ($attributes['role'] === RoleName::SuperAdmin->value && ! $currentUser->hasRole(RoleName::SuperAdmin->value)) {
            throw ValidationException::withMessages(['role' => 'Only a Super Admin can assign the Super Admin role.']);
        }

        if ($user->is($currentUser) && ! $attributes['is_active']) {
            throw ValidationException::withMessages(['is_active' => 'You cannot deactivate your own account.']);
        }

        if ($user->hasRole(RoleName::SuperAdmin->value) && $attributes['role'] !== RoleName::SuperAdmin->value) {
            throw ValidationException::withMessages(['role' => 'The Super Admin role is protected.']);
        }

        if ($user->hasRole(RoleName::SuperAdmin->value) && ! $currentUser->hasRole(RoleName::SuperAdmin->value)) {
            throw ValidationException::withMessages(['role' => 'Only a Super Admin can manage a Super Admin account.']);
        }

        $user->update([
            'is_active' => $attributes['is_active'],
            'deactivated_at' => $attributes['is_active'] ? null : now(),
        ]);
        $user->syncRoles([$attributes['role']]);

        activity()
            ->performedOn($user)
            ->withProperties(['role' => $attributes['role'], 'is_active' => $attributes['is_active']])
            ->log('User access updated.');

        return back()->with('success', 'User access updated.');
    }

    public function resendVerification(User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        if (! $user->hasVerifiedEmail()) {
            $user->sendEmailVerificationNotification();
        }

        activity()->performedOn($user)->log('Verification email resent.');

        return back()->with('success', 'Verification email sent.');
    }

    public function resetTwoFactor(User $user): RedirectResponse
    {
        $this->authorize('update', $user);
        $user->two_factor_secret = null;
        $user->two_factor_recovery_codes = null;
        $user->two_factor_confirmed_at = null;
        $user->save();

        activity()->performedOn($user)->log('Two-factor authentication reset.');

        return back()->with('success', 'Two-factor authentication reset.');
    }
}
