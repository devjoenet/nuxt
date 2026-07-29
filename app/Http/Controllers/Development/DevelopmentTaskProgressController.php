<?php

declare(strict_types=1);

namespace App\Http\Controllers\Development;

use App\Actions\UpdateDevelopmentTaskProgress;
use App\Http\Controllers\Controller;
use App\Http\Requests\Development\UpdateDevelopmentTaskProgressRequest;
use App\Models\DevelopmentTask;
use Illuminate\Http\RedirectResponse;

class DevelopmentTaskProgressController extends Controller
{
    public function update(
        UpdateDevelopmentTaskProgressRequest $request,
        DevelopmentTask $developmentTask,
        UpdateDevelopmentTaskProgress $updateDevelopmentTaskProgress,
    ): RedirectResponse {
        $task = $updateDevelopmentTaskProgress->handle(
            $developmentTask,
            $request->toDto(),
            $this->authenticatedUser($request),
        );

        return back()->with('success', "Task moved to {$task->status->label()}.");
    }
}
