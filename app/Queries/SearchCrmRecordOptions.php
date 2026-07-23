<?php

declare(strict_types=1);

namespace App\Queries;

use App\Data\CrmOptionSearchData;
use App\Models\Contact;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class SearchCrmRecordOptions
{
    /** @return Collection<int, Contact> */
    public function contacts(User $user, CrmOptionSearchData $data): Collection
    {
        return Contact::query()
            ->visibleTo($user)
            ->whereNull('archived_at')
            ->where(function (Builder $query) use ($data): void {
                $query->where('name', 'like', "%{$data->query}%")
                    ->orWhere('email', 'like', "%{$data->query}%");
            })
            ->orderBy('name')
            ->orderBy('uuid')
            ->limit(20)
            ->get();
    }

    /** @return Collection<int, Organization> */
    public function organizations(User $user, CrmOptionSearchData $data): Collection
    {
        return Organization::query()
            ->visibleTo($user)
            ->whereNull('archived_at')
            ->where(function (Builder $query) use ($data): void {
                $query->where('name', 'like', "%{$data->query}%")
                    ->orWhere('website', 'like', "%{$data->query}%");
            })
            ->orderBy('name')
            ->orderBy('uuid')
            ->limit(20)
            ->get();
    }
}
