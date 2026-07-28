<?php

declare(strict_types=1);

namespace App\Http\Controllers\Crm;

use App\Actions\ArchiveLead;
use App\Actions\RestoreLead;
use App\Http\Controllers\Controller;
use App\Models\Lead;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LeadArchiveController extends Controller
{
    public function store(Request $request, Lead $lead, ArchiveLead $action): RedirectResponse
    {
        $user = $this->authenticatedUser($request);
        abort_unless($lead->isVisibleTo($user), 404);
        $this->authorize('archive', $lead);
        $action->handle($lead, $user);

        return to_route('crm.leads.index')->with('success', 'Lead archived.');
    }

    public function destroy(Request $request, Lead $lead, RestoreLead $action): RedirectResponse
    {
        $user = $this->authenticatedUser($request);
        abort_unless($lead->isVisibleTo($user), 404);
        $this->authorize('archive', $lead);
        $action->handle($lead, $user);

        return to_route('crm.leads.show', $lead)->with('success', 'Lead restored.');
    }
}
