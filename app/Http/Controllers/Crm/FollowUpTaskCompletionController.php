<?php

declare(strict_types=1);

namespace App\Http\Controllers\Crm;

use App\Actions\CompleteFollowUpTask;
use App\Http\Controllers\Controller;
use App\Models\FollowUpTask;
use App\Models\Lead;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FollowUpTaskCompletionController extends Controller
{
    public function __invoke(Request $request, Lead $lead, FollowUpTask $task, CompleteFollowUpTask $action): RedirectResponse
    {
        $user = $this->authenticatedUser($request);
        abort_unless($task->lead_id === $lead->id, 404);
        abort_unless($lead->isVisibleTo($user), 404);
        $this->authorize('update', $lead);
        $action->handle($lead, $task, $user);

        return back()->with('success', 'Follow-up completed.');
    }
}
