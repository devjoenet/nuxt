<?php

declare(strict_types=1);

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use Illuminate\Http\RedirectResponse;

class LeadArchiveController extends Controller
{
    public function store(Lead $lead): RedirectResponse
    {
        $this->authorize('archive', $lead);
        $lead->update(['archived_at' => now()]);

        return to_route('crm.leads.index')->with('success', 'Lead archived.');
    }

    public function destroy(Lead $lead): RedirectResponse
    {
        $this->authorize('archive', $lead);
        $lead->update(['archived_at' => null]);

        return to_route('crm.leads.show', $lead)->with('success', 'Lead restored.');
    }
}
