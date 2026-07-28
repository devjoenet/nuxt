<?php

declare(strict_types=1);

namespace App\Queries;

use App\Data\ContactIndexData;
use App\Models\Contact;
use App\Models\Lead;
use App\Models\Organization;
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
            ->when($filters->search, function (Builder $query, string $search) use ($user): void {
                $query->where(function (Builder $query) use ($search, $user): void {
                    $query->where('name', 'like', sprintf('%%%s%%', $search))
                        ->orWhere('email', 'like', sprintf('%%%s%%', $search))
                        ->orWhere('phone', 'like', sprintf('%%%s%%', $search))
                        ->orWhereIn('organization_id', Organization::query()
                            ->visibleTo($user)
                            ->where('name', 'like', sprintf('%%%s%%', $search))
                            ->select('id'));
                });
            })
            ->when($filters->organizationId, function (Builder $query, string $organizationId) use ($user): void {
                $query->whereIn('organization_id', Organization::query()
                    ->visibleTo($user)
                    ->where('uuid', $organizationId)
                    ->select('id'));
            })
            ->when($filters->ownerId, fn (Builder $query, int $ownerId): Builder => $query->where('owner_id', $ownerId))
            ->orderBy($filters->sort->value, $filters->direction->value);
    }
}
