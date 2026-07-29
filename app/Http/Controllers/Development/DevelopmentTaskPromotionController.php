<?php

declare(strict_types=1);

namespace App\Http\Controllers\Development;

use App\Actions\PromoteDevelopmentTask;
use App\Http\Controllers\Controller;
use App\Models\DevelopmentTask;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DevelopmentTaskPromotionController extends Controller
{
    public function __invoke(
        Request $request,
        DevelopmentTask $developmentTask,
        PromoteDevelopmentTask $promoteDevelopmentTask,
    ): RedirectResponse {
        $this->authorize('review', $developmentTask);
        $promoteDevelopmentTask->handle(
            $developmentTask,
            $this->authenticatedUser($request),
        );

        return to_route('development.tasks.show', $developmentTask)
            ->with('success', 'Request added to the roadmap.');
    }
}
