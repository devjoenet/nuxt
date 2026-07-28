<?php

declare(strict_types=1);

namespace App\Queries;

use App\Data\LeadIndexData;
use App\Models\Contact;
use App\Models\Lead;
use App\Models\Organization;
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
            ->when($filters->search, function (Builder $query, string $search) use ($user): void {
                $query->where(function (Builder $query) use ($search, $user): void {
                    $query->whereIn('contact_id', Contact::query()
                        ->withTrashed()
                        ->visibleTo($user)
                        ->where(function (Builder $query) use ($search): void {
                            $query->where('name', 'like', sprintf('%%%s%%', $search))
                                ->orWhere('email', 'like', sprintf('%%%s%%', $search));
                        })
                        ->select('id'))
                        ->orWhereIn('organization_id', Organization::query()
                            ->visibleTo($user)
                            ->where('name', 'like', sprintf('%%%s%%', $search))
                            ->select('id'));
                });
            })
            ->when($filters->status, fn (Builder $query): Builder => $query->where('status', $filters->status))
            ->when($filters->source, fn (Builder $query): Builder => $query->where('source', $filters->source))
            ->when($filters->ownerId, fn (Builder $query, int $ownerId): Builder => $query->where('owner_id', $ownerId))
            ->orderBy($filters->sort->value, $filters->direction->value);
    }
}
