<?php

declare(strict_types=1);

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFollowUpTaskRequest;
use App\Models\Lead;
use Illuminate\Http\RedirectResponse;

class FollowUpTaskController extends Controller
{
    public function store(StoreFollowUpTaskRequest $request, Lead $lead): RedirectResponse
    {
        $lead->followUpTasks()->create([
            ...$request->validated(),
            'assigned_to' => $lead->owner_id,
        ]);

        return back()->with('success', 'Follow-up created.');
    }
}
