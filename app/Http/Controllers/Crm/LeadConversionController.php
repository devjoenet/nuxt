<?php

declare(strict_types=1);

namespace App\Http\Controllers\Crm;

use App\Actions\ConvertLead;
use App\Http\Controllers\Controller;
use App\Models\Lead;
use Illuminate\Http\RedirectResponse;

class LeadConversionController extends Controller
{
    public function __invoke(Lead $lead, ConvertLead $action): RedirectResponse
    {
        $this->authorize('convert', $lead);
        $action->handle($lead);

        return to_route('crm.leads.show', $lead)->with('success', 'Lead converted to a client.');
    }
}
