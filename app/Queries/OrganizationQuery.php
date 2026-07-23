<?php

declare(strict_types=1);

namespace App\Queries;

use App\Data\OrganizationIndexData;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class OrganizationQuery
{
    /** @return Builder<Organization> */
    public function build(User $user, OrganizationIndexData $filters): Builder
    {
        return Organization::query()
            ->visibleTo($user)
            ->with('owner')
            ->withCount([
                'contacts' => fn (Builder $query): Builder => $query->visibleTo($user),
                'leads' => fn (Builder $query): Builder => $query->visibleTo($user),
            ])
            ->whereNull('archived_at', not: $filters->archived)
            ->when($filters->search, function (Builder $query, string $search): void {
                $query->where(fn (Builder $query): Builder => $query
                    ->where('name', 'like', sprintf('%%%s%%', $search)));
            })
            ->when($filters->ownerId, fn (Builder $query, int $ownerId): Builder => $query->where('owner_id', $ownerId))
            ->orderBy($filters->sort->value, $filters->direction->value);
    }
}
