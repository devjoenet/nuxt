<?php

declare(strict_types=1);

namespace App\Queries;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class ContactQuery
{
    /** @param array<string, mixed> $filters */
    public function build(User $user, array $filters): Builder
    {
        $sort = in_array($filters['sort'] ?? null, ['name', 'created_at'], true) ? $filters['sort'] : 'created_at';
        $direction = ($filters['direction'] ?? null) === 'asc' ? 'asc' : 'desc';

        return Contact::query()
            ->visibleTo($user)
            ->with(['organization', 'owner'])
            ->withCount('leads')
            ->when($filters['archived'] ?? false, fn (Builder $query): Builder => $query->whereNotNull('archived_at'))
            ->when(! ($filters['archived'] ?? false), fn (Builder $query): Builder => $query->whereNull('archived_at'))
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('name', 'like', sprintf('%%%s%%', $search))
                        ->orWhere('email', 'like', sprintf('%%%s%%', $search))
                        ->orWhere('phone', 'like', sprintf('%%%s%%', $search))
                        ->orWhereHas('organization', fn (Builder $query): Builder => $query->where('name', 'like', sprintf('%%%s%%', $search)));
                });
            })
            ->when($filters['organization'] ?? null, fn (Builder $query, string $organization): Builder => $query->where('organization_id', $organization))
            ->when($filters['owner'] ?? null, fn (Builder $query, string $owner): Builder => $query->where('owner_id', $owner))
            ->orderBy($sort, $direction);
    }
}
