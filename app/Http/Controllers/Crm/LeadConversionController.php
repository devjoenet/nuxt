<?php

declare(strict_types=1);

namespace App\Http\Controllers\Crm;

use App\Actions\ConvertLead;
use App\Http\Controllers\Controller;
use App\Models\Lead;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LeadConversionController extends Controller
{
    public function __invoke(Request $request, Lead $lead, ConvertLead $action): RedirectResponse
    {
        $user = $this->authenticatedUser($request);
        abort_unless($lead->isVisibleTo($user), 404);
        $this->authorize('convert', $lead);
        $action->handle($lead, $user);

        return to_route('crm.leads.show', $lead)->with('success', 'Lead converted to a client.');
    }
}
