<?php

declare(strict_types=1);

namespace App\Http\Controllers\Crm;

use App\Enums\CrmActivityType;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLeadNoteRequest;
use App\Models\Lead;
use Illuminate\Http\RedirectResponse;

class LeadNoteController extends Controller
{
    public function store(StoreLeadNoteRequest $request, Lead $lead): RedirectResponse
    {
        $lead->notes()->create([...$request->validated(), 'user_id' => $request->user()->id]);
        $lead->activities()->create([
            'user_id' => $request->user()->id,
            'type' => CrmActivityType::NoteAdded,
            'description' => 'A note was added.',
        ]);

        return back()->with('success', 'Note added.');
    }
}
