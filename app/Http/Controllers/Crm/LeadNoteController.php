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
        $user = $this->authenticatedUser($request);
        $lead->notes()->create([...$request->validated(), 'user_id' => $user->id]);
        $lead->activities()->create([
            'user_id' => $user->id,
            'type' => CrmActivityType::NoteAdded,
            'description' => 'A note was added.',
        ]);

        return back()->with('success', 'Note added.');
    }
}
