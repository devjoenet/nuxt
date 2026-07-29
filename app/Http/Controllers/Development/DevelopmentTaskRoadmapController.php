<?php

declare(strict_types=1);

namespace App\Http\Controllers\Development;

use App\Actions\UpdateDevelopmentTaskRoadmap;
use App\Http\Controllers\Controller;
use App\Http\Requests\Development\UpdateDevelopmentTaskRoadmapRequest;
use App\Models\DevelopmentTask;
use Illuminate\Http\RedirectResponse;

class DevelopmentTaskRoadmapController extends Controller
{
    public function update(
        UpdateDevelopmentTaskRoadmapRequest $request,
        DevelopmentTask $developmentTask,
        UpdateDevelopmentTaskRoadmap $updateDevelopmentTaskRoadmap,
    ): RedirectResponse {
        $updateDevelopmentTaskRoadmap->handle(
            $developmentTask,
            $request->toDto(),
            $this->authenticatedUser($request),
        );

        return back()->with('success', 'Roadmap details updated.');
    }
}
