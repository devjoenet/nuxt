<?php

declare(strict_types=1);

namespace App\Http\Controllers\Development;

use App\Actions\AddDevelopmentTaskNote;
use App\Http\Controllers\Controller;
use App\Http\Requests\Development\StoreDevelopmentTaskNoteRequest;
use App\Models\DevelopmentTask;
use App\Models\DevelopmentTaskNote;
use Illuminate\Http\RedirectResponse;

class DevelopmentTaskNoteController extends Controller
{
    public function store(
        StoreDevelopmentTaskNoteRequest $request,
        DevelopmentTask $developmentTask,
        AddDevelopmentTaskNote $addDevelopmentTaskNote,
    ): RedirectResponse {
        $data = $request->toDto();
        $this->authorize('create', [
            DevelopmentTaskNote::class,
            $developmentTask,
            $data->audience,
        ]);
        $addDevelopmentTaskNote->handle(
            $developmentTask,
            $data,
            $this->authenticatedUser($request),
        );

        return back()->with('success', 'Note added.');
    }
}
