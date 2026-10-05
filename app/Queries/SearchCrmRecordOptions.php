<?php

declare(strict_types=1);

namespace App\Queries;

use App\Data\CrmOptionSearchData;
use App\Models\Contact;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\Paginator;

class SearchCrmRecordOptions
{
    /** @return Paginator<int, Contact> */
    public function contacts(User $user, CrmOptionSearchData $data): Paginator
    {
        return Contact::query()
            ->visibleTo($user)
            ->whereNull('archived_at')
            ->when($data->query !== '', fn (Builder $query): Builder => $query->where(function (Builder $query) use ($data): void {
                $query->where('name', 'like', "%{$data->query}%")
                    ->orWhere('email', 'like', "%{$data->query}%");
            }))
            ->orderBy('name')
            ->orderBy('uuid')
            ->simplePaginate(20, ['id', 'uuid', 'name', 'email'], page: $data->page);
    }

    /** @return Paginator<int, Organization> */
    public function organizations(User $user, CrmOptionSearchData $data): Paginator
    {
        return Organization::query()
            ->visibleTo($user)
            ->whereNull('archived_at')
            ->when($data->query !== '', fn (Builder $query): Builder => $query->where(function (Builder $query) use ($data): void {
                $query->where('name', 'like', "%{$data->query}%")
                    ->orWhere('website', 'like', "%{$data->query}%");
            }))
            ->orderBy('name')
            ->orderBy('uuid')
            ->simplePaginate(20, ['id', 'uuid', 'name', 'website'], page: $data->page);
    }
}
