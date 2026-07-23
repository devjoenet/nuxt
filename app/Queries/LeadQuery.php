<?php

declare(strict_types=1);

namespace App\Queries;

use App\Data\LeadIndexData;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class LeadQuery
{
    /** @return Builder<Lead> */
    public function build(User $user, LeadIndexData $filters): Builder
    {
        return Lead::query()
            ->visibleTo($user)
            ->with(['contact', 'organization', 'owner'])
            ->whereNull('archived_at', not: $filters->archived)
            ->when($filters->search, function (Builder $query, string $search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->whereHas('contact', fn (Builder $query): Builder => $query->where('name', 'like', sprintf('%%%s%%', $search))->orWhere('email', 'like', sprintf('%%%s%%', $search)))
                        ->orWhereHas('organization', fn (Builder $query): Builder => $query->where('name', 'like', sprintf('%%%s%%', $search)));
                });
            })
            ->when($filters->status, fn (Builder $query): Builder => $query->where('status', $filters->status))
            ->when($filters->source, fn (Builder $query): Builder => $query->where('source', $filters->source))
            ->when($filters->ownerId, fn (Builder $query, int $ownerId): Builder => $query->where('owner_id', $ownerId))
            ->orderBy($filters->sort->value, $filters->direction->value);
    }
}
