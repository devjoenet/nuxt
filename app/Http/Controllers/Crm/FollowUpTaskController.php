<?php

declare(strict_types=1);

namespace App\Http\Controllers\Crm;

use App\Actions\CreateFollowUpTask;
use App\Data\FollowUpTaskData;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFollowUpTaskRequest;
use App\Models\Lead;
use Illuminate\Http\RedirectResponse;

class FollowUpTaskController extends Controller
{
    public function store(StoreFollowUpTaskRequest $request, Lead $lead, CreateFollowUpTask $action): RedirectResponse
    {
        $action->handle(
            $lead,
            FollowUpTaskData::fromValidated($request->validated()),
            $this->authenticatedUser($request),
        );

        return back()->with('success', 'Follow-up created.');
    }
}
