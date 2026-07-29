<?php

declare(strict_types=1);

namespace App\Http\Controllers\Development;

use App\Actions\AttachDevelopmentTaskLink;
use App\Http\Controllers\Controller;
use App\Http\Requests\Development\StoreDevelopmentTaskLinkRequest;
use App\Models\DevelopmentTask;
use Illuminate\Http\RedirectResponse;

class DevelopmentTaskLinkController extends Controller
{
    public function store(
        StoreDevelopmentTaskLinkRequest $request,
        DevelopmentTask $developmentTask,
        AttachDevelopmentTaskLink $attachDevelopmentTaskLink,
    ): RedirectResponse {
        $attachDevelopmentTaskLink->handle(
            $developmentTask,
            $request->toDto(),
            $this->authenticatedUser($request),
        );

        return back()->with('success', 'Task link attached.');
    }
}
