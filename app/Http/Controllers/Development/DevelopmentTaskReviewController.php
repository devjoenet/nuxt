<?php

declare(strict_types=1);

namespace App\Http\Controllers\Development;

use App\Actions\ReviewDevelopmentTask;
use App\Enums\Permission;
use App\Http\Controllers\Controller;
use App\Http\Resources\DevelopmentTaskResource;
use App\Models\DevelopmentTask;
use App\Queries\DevelopmentTaskQuery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class DevelopmentTaskReviewController extends Controller
{
    public function index(Request $request, DevelopmentTaskQuery $query): Response
    {
        Gate::authorize(Permission::DevelopmentRequestsReview->value);

        return Inertia::render('Development/Review/Index', [
            'requests' => DevelopmentTaskResource::collection(
                $query->reviewQueue()->paginate(20),
            ),
        ]);
    }

    public function store(
        Request $request,
        DevelopmentTask $developmentTask,
        ReviewDevelopmentTask $reviewDevelopmentTask,
    ): RedirectResponse {
        $this->authorize('review', $developmentTask);
        $reviewDevelopmentTask->handle(
            $developmentTask,
            $this->authenticatedUser($request),
        );

        return to_route('development.tasks.show', $developmentTask)
            ->with('success', 'Review started.');
    }
}
