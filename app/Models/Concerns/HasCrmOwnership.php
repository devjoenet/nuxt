<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Models\User;
use App\Modules\WorkspaceUser;
use App\Support\CrmAccess;
use App\Support\WorkspaceUsers;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasCrmOwnership
{
    /** @return BelongsTo<User, $this> */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(WorkspaceUsers::model(), 'owner_id');
    }

    public function isVisibleTo(WorkspaceUser $user): bool
    {
        if (CrmAccess::administrator($user)) {
            return true;
        }

        if ($this->owner_id === null) {
            return true;
        }

        return $this->owner_id === $user->workspaceId();
    }

    /** @param Builder<static> $query */
    #[Scope]
    protected function visibleTo(Builder $query, WorkspaceUser $user): void
    {
        self::constrainVisibility($query, $user);
    }

    /** @param Builder<static> $query */
    public static function constrainVisibility(Builder $query, WorkspaceUser $user): void
    {
        if (CrmAccess::administrator($user)) {
            return;
        }

        $query->where(function (Builder $query) use ($user): void {
            $query->where('owner_id', $user->workspaceId())->orWhereNull('owner_id');
        });
    }
}
