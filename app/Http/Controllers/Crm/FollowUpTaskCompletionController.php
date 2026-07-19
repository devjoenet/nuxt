<?php

declare(strict_types=1);

namespace App\Http\Controllers\Crm;

use App\Enums\CrmActivityType;
use App\Enums\FollowUpTaskStatus;
use App\Http\Controllers\Controller;
use App\Models\FollowUpTask;
use App\Models\Lead;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FollowUpTaskCompletionController extends Controller
{
    public function __invoke(Request $request, Lead $lead, FollowUpTask $task): RedirectResponse
    {
        $user = $this->authenticatedUser($request);
        abort_unless($task->lead_id === $lead->id, 404);
        abort_unless($lead->isVisibleTo($user), 404);
        $this->authorize('update', $lead);
        $task->update(['status' => FollowUpTaskStatus::Completed, 'completed_at' => now()]);
        $lead->activities()->create([
            'user_id' => auth()->id(),
            'type' => CrmActivityType::TaskCompleted,
            'description' => 'Follow-up completed: '.$task->title,
        ]);

        return back()->with('success', 'Follow-up completed.');
    }
}
