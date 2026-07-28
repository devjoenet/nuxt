<?php

declare(strict_types=1);

namespace App\Http\Controllers\Crm;

use App\Actions\CreateLeadNote;
use App\Data\LeadNoteData;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLeadNoteRequest;
use App\Models\Lead;
use Illuminate\Http\RedirectResponse;

class LeadNoteController extends Controller
{
    public function store(StoreLeadNoteRequest $request, Lead $lead, CreateLeadNote $action): RedirectResponse
    {
        $action->handle($lead, LeadNoteData::fromValidated($request->validated()), $this->authenticatedUser($request));

        return back()->with('success', 'Note added.');
    }
}
