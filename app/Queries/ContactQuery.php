<?php

declare(strict_types=1);

namespace App\Queries;

use App\Data\ContactIndexData;
use App\Models\Contact;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class ContactQuery
{
    /** @return Builder<Contact> */
    public function build(User $user, ContactIndexData $filters): Builder
    {
        return Contact::query()
            ->visibleTo($user)
            ->with(['organization', 'owner'])
            ->withCount(['leads' => function (Builder $query) use ($user): void {
                Lead::constrainVisibility($query, $user);
            }])
            ->whereNull('archived_at', not: $filters->archived)
            ->when($filters->search, function (Builder $query, string $search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('name', 'like', sprintf('%%%s%%', $search))
                        ->orWhere('email', 'like', sprintf('%%%s%%', $search))
                        ->orWhere('phone', 'like', sprintf('%%%s%%', $search))
                        ->orWhereHas('organization', fn (Builder $query): Builder => $query->where('name', 'like', sprintf('%%%s%%', $search)));
                });
            })
            ->when($filters->organizationId, fn (Builder $query, string $organizationId): Builder => $query->whereHas('organization', fn (Builder $query): Builder => $query->where('uuid', $organizationId)))
            ->when($filters->ownerId, fn (Builder $query, int $ownerId): Builder => $query->where('owner_id', $ownerId))
            ->orderBy($filters->sort->value, $filters->direction->value);
    }
}
