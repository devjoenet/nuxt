<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\InviteUser;
use App\Actions\ResendUserVerification;
use App\Actions\ResetUserTwoFactorAuthentication;
use App\Actions\UpdateAdminUserAccess;
use App\Data\AdminUserAccessData;
use App\Data\InviteUserData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAdminUserRequest;
use App\Http\Requests\Admin\UpdateAdminUserRequest;
use App\Http\Resources\AdminUserResource;
use App\Models\User;
use App\Support\WorkspaceUsers;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class AdminUserController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', WorkspaceUsers::model());

        $filters = $request->validate(['status' => ['nullable', Rule::in(['pending', 'active'])]]);
        $users = WorkspaceUsers::model()::query()->with('roles')
            ->when(($filters['status'] ?? null) === 'pending', fn (Builder $query): Builder => $query->pendingInvitation())
            ->when(($filters['status'] ?? null) === 'active', fn (Builder $query): Builder => $query->where('is_active', true));

        return Inertia::render('Admin/Users/Index', [
            'filters' => $filters,
            'hasUntrackedInvitations' => WorkspaceUsers::model()::query()->untrackedInvitation()->exists(),
            'users' => AdminUserResource::collection($users->latest()->paginate(20)->withQueryString()),
            'roles' => Role::query()->orderBy('name')->get(['name'])->map(fn (Role $role): array => [
                'label' => $role->name,
                'value' => $role->name,
            ]),
        ]);
    }

    public function store(StoreAdminUserRequest $request, InviteUser $inviteUser): RedirectResponse
    {
        $this->authorize('create', WorkspaceUsers::model());

        $inviteUser->handle(
            InviteUserData::fromValidated($request->validated()),
            $this->authenticatedUser($request),
        );

        return to_route('admin.users.index')->with('success', 'Invitation sent.');
    }

    public function update(UpdateAdminUserRequest $request, User $user, UpdateAdminUserAccess $action): RedirectResponse
    {
        $this->authorize('update', $user);
        $action->handle(
            $user,
            AdminUserAccessData::fromValidated($request->validated()),
            $this->authenticatedUser($request),
        );

        return back()->with('success', 'User access updated.');
    }

    public function resendVerification(User $user, ResendUserVerification $resendUserVerification): RedirectResponse
    {
        $this->authorize('update', $user);

        $resendUserVerification->handle($user);

        return back()->with('success', 'Verification email sent.');
    }

    public function resetTwoFactor(User $user, ResetUserTwoFactorAuthentication $resetUserTwoFactorAuthentication): RedirectResponse
    {
        $this->authorize('update', $user);
        $resetUserTwoFactorAuthentication->handle($user);

        return back()->with('success', 'Two-factor authentication reset.');
    }
}
