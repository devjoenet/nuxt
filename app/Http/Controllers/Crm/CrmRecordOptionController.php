<?php

declare(strict_types=1);

namespace App\Http\Controllers\Crm;

use App\Data\CrmOptionSearchData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Crm\SearchCrmRecordOptionsRequest;
use App\Http\Resources\CrmRecordOptionResource;
use App\Models\User;
use App\Queries\SearchCrmRecordOptions;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CrmRecordOptionController extends Controller
{
    public function contacts(SearchCrmRecordOptionsRequest $request, SearchCrmRecordOptions $query): AnonymousResourceCollection
    {
        return CrmRecordOptionResource::collection($query->contacts($this->user($request), CrmOptionSearchData::fromValidated($request->validated())));
    }

    private function user(SearchCrmRecordOptionsRequest $request): User
    {
        return $this->authenticatedUser($request);
    }

    public function organizations(SearchCrmRecordOptionsRequest $request, SearchCrmRecordOptions $query): AnonymousResourceCollection
    {
        return CrmRecordOptionResource::collection($query->organizations($this->user($request), CrmOptionSearchData::fromValidated($request->validated())));
    }
}
