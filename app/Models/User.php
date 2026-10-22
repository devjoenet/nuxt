<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Override;
use SoutheastCode\Modules\WorkspaceUser;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property Carbon|null $invited_at
 * @property Carbon|null $invitation_accepted_at
 */
#[Fillable(['name', 'email', 'password', 'is_active', 'deactivated_at', 'invited_at', 'invitation_accepted_at'])]
#[Hidden(['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes'])]
class User extends Authenticatable implements MustVerifyEmail, WorkspaceUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;

    use HasRoles;
    use HasUuids;
    use LogsActivity;
    use Notifiable;
    use TwoFactorAuthenticatable;

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }

    /** @param Builder<static> $query */
    #[Scope]
    protected function pendingInvitation(Builder $query): void
    {
        $query->whereNotNull('invited_at')->whereNull('invitation_accepted_at')->where('is_active', true);
    }

    /** @param Builder<static> $query */
    #[Scope]
    protected function untrackedInvitation(Builder $query): void
    {
        $query->whereNull('invited_at')->where('is_active', true)
            ->whereIn('id', Activity::query()->select('subject_id')
                ->where('subject_type', $this->getMorphClass())
                ->where('description', 'User invitation sent.'));
    }

    public function workspaceId(): int
    {
        return (int) $this->getKey();
    }

    public function workspaceUuid(): string
    {
        return $this->uuid;
    }

    public function workspaceName(): string
    {
        return $this->name;
    }

    public function workspaceEmail(): string
    {
        return $this->email;
    }

    public function workspaceActive(): bool
    {
        return $this->is_active;
    }

    public function workspaceCan(string $ability, mixed $arguments = []): bool
    {
        return $this->can($ability, $arguments);
    }

    /** @param list<string> $roles */
    public function workspaceHasAnyRole(array $roles): bool
    {
        return $this->hasAnyRole($roles);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('users')
            ->logOnly(['name', 'email', 'is_active', 'deactivated_at', 'email_verified_at', 'invited_at', 'invitation_accepted_at'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    /** @return array<string, string> */
    #[Override]
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'is_active' => 'boolean',
            'deactivated_at' => 'datetime',
            'password' => 'hashed',
            'invited_at' => 'datetime',
            'invitation_accepted_at' => 'datetime',
        ];
    }

    /** @return array<int, string>  */
    #[Override]
    public function uniqueIds(): array
    {
        return ['uuid'];
    }
}
