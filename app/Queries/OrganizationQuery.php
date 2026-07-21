<?php

declare(strict_types=1);

namespace App\Queries;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class OrganizationQuery
{
    /**
     * @param  array<string, mixed>  $filters
     * @return Builder<Organization>
     */
    public function build(User $user, array $filters): Builder
    {
        $sort = in_array($filters['sort'] ?? null, ['name', 'created_at'], true) ? $filters['sort'] : 'created_at';
        $direction = ($filters['direction'] ?? null) === 'asc' ? 'asc' : 'desc';

        return Organization::query()
            ->visibleTo($user)
            ->with('owner')
            ->withCount(['contacts', 'leads'])
            ->when($filters['archived'] ?? false, fn (Builder $query): Builder => $query->whereNotNull('archived_at'))
            ->when(! ($filters['archived'] ?? false), fn (Builder $query): Builder => $query->whereNull('archived_at'))
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(fn (Builder $query): Builder => $query
                    ->where('name', 'like', sprintf('%%%s%%', $search)));
            })
            ->when($filters['owner'] ?? null, fn (Builder $query, string $owner): Builder => $query->where('owner_id', $owner))
            ->orderBy($sort, $direction);
    }
}
