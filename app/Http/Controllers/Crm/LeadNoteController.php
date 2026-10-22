<?php

declare(strict_types=1);

namespace App\Http\Controllers\Crm;

use App\Actions\CreateLeadNote;
use App\Data\LeadNoteData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Crm\StoreLeadNoteRequest;
use Illuminate\Http\RedirectResponse;
use SoutheastCode\Crm\Leads\Models\Lead;

class LeadNoteController extends Controller
{
    public function store(StoreLeadNoteRequest $request, Lead $lead, CreateLeadNote $action): RedirectResponse
    {
        $action->handle($lead, LeadNoteData::fromValidated($request->validated()), $this->authenticatedUser($request));

        return back()->with('success', 'Note added.');
    }
}
