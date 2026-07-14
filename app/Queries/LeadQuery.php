<?php

declare(strict_types=1);

namespace App\Queries;

use App\Models\Lead;
use Illuminate\Database\Eloquent\Builder;

class LeadQuery
{
    public function build(array $filters): Builder
    {
        $sort = in_array($filters['sort'] ?? null, ['created_at', 'status'], true) ? $filters['sort'] : 'created_at';
        $direction = ($filters['direction'] ?? null) === 'asc' ? 'asc' : 'desc';

        return Lead::query()
            ->with(['contact', 'organization', 'owner'])
            ->when($filters['archived'] ?? false, fn (Builder $query): Builder => $query->whereNotNull('archived_at'))
            ->when(! ($filters['archived'] ?? false), fn (Builder $query): Builder => $query->whereNull('archived_at'))
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->whereHas('contact', fn (Builder $query): Builder => $query->where('name', 'like', sprintf('%%%s%%', $search))->orWhere('email', 'like', sprintf('%%%s%%', $search)))
                        ->orWhereHas('organization', fn (Builder $query): Builder => $query->where('name', 'like', sprintf('%%%s%%', $search)));
                });
            })
            ->when($filters['status'] ?? null, fn (Builder $query, string $status): Builder => $query->where('status', $status))
            ->when($filters['source'] ?? null, fn (Builder $query, string $source): Builder => $query->where('source', $source))
            ->when($filters['owner'] ?? null, fn (Builder $query, string $owner): Builder => $query->where('owner_id', $owner))
            ->orderBy($sort, $direction);
    }
}
