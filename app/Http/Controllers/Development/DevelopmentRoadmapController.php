<?php

declare(strict_types=1);

namespace App\Http\Controllers\Development;

use App\Enums\DevelopmentTaskStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\DevelopmentTaskResource;
use App\Queries\DevelopmentTaskQuery;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DevelopmentRoadmapController extends Controller
{
    public function __invoke(Request $request, DevelopmentTaskQuery $query): Response
    {
        $tasks = DevelopmentTaskResource::collection($query->roadmap()->get())
            ->resolve($request);
        $tasksByStatus = collect($tasks)->groupBy('status');

        return Inertia::render('Development/Roadmap/Index', [
            'tasks' => [
                DevelopmentTaskStatus::Planned->value => $tasksByStatus
                    ->get(DevelopmentTaskStatus::Planned->value, collect())
                    ->values()
                    ->all(),
                DevelopmentTaskStatus::InProgress->value => $tasksByStatus
                    ->get(DevelopmentTaskStatus::InProgress->value, collect())
                    ->values()
                    ->all(),
                DevelopmentTaskStatus::Blocked->value => $tasksByStatus
                    ->get(DevelopmentTaskStatus::Blocked->value, collect())
                    ->values()
                    ->all(),
            ],
        ]);
    }
}
