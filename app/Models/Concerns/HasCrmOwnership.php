<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasCrmOwnership
{
    /** @return BelongsTo<User, $this> */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function isVisibleTo(User $user): bool
    {
        if ($user->isCrmAdministrator()) {
            return true;
        }

        if ($this->owner_id === null) {
            return true;
        }

        return $this->owner_id === $user->id;
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isCrmAdministrator()) {
            return $query;
        }

        return $query->where(function (Builder $query) use ($user): void {
            $query->where('owner_id', $user->id)->orWhereNull('owner_id');
        });
    }
}
