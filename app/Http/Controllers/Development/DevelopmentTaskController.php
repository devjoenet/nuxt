<?php

declare(strict_types=1);

namespace App\Http\Controllers\Development;

use App\Actions\SubmitDevelopmentTask;
use App\Enums\DevelopmentRequestType;
use App\Enums\DevelopmentTaskArea;
use App\Enums\DevelopmentTaskLinkType;
use App\Enums\DevelopmentTaskStatus;
use App\Enums\Permission;
use App\Enums\TaskPriority;
use App\Http\Controllers\Controller;
use App\Http\Requests\Development\StoreDevelopmentTaskRequest;
use App\Http\Resources\DevelopmentTaskResource;
use App\Models\DevelopmentTask;
use App\Models\User;
use App\Queries\DevelopmentTaskQuery;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DevelopmentTaskController extends Controller
{
    public function overview(Request $request, DevelopmentTaskQuery $query): Response
    {
        $this->authorize('viewAny', DevelopmentTask::class);
        $user = $this->authenticatedUser($request);

        return Inertia::render('Development/Overview', [
            'requests' => DevelopmentTaskResource::collection(
                $query->forSubmitter($user)->limit(5)->get(),
            )->resolve($request),
            'reviewQueueCount' => $user->can(Permission::DevelopmentRequestsReview->value)
                ? $query->reviewQueue()->count()
                : 0,
            'roadmapCount' => $user->can(Permission::DevelopmentRoadmapManage->value)
                ? DevelopmentTask::query()->whereIn('status', [
                    DevelopmentTaskStatus::Planned,
                    DevelopmentTaskStatus::InProgress,
                    DevelopmentTaskStatus::Blocked,
                ])->count()
                : 0,
            'canCreate' => $user->can('create', DevelopmentTask::class),
            'canReview' => $user->can(Permission::DevelopmentRequestsReview->value),
        ]);
    }

    public function index(Request $request, DevelopmentTaskQuery $query): Response
    {
        $this->authorize('viewAny', DevelopmentTask::class);
        $user = $this->authenticatedUser($request);

        return Inertia::render('Development/Requests/Index', [
            'requests' => DevelopmentTaskResource::collection(
                $query->forSubmitter($user)->paginate(15),
            ),
            'canCreate' => $user->can('create', DevelopmentTask::class),
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', DevelopmentTask::class);

        return Inertia::render('Development/Requests/Create', [
            'requestTypes' => $this->requestTypeOptions(),
            'affectedAreas' => $this->affectedAreaOptions(),
        ]);
    }

    /** @return array<int, array{label: string, value: string}> */
    private function requestTypeOptions(): array
    {
        return array_map(fn (DevelopmentRequestType $type): array => [
            'label' => $type->label(),
            'value' => $type->value,
        ], DevelopmentRequestType::cases());
    }

    /** @return array<int, array{label: string, value: string}> */
    private function affectedAreaOptions(): array
    {
        return array_map(fn (DevelopmentTaskArea $area): array => [
            'label' => $area->label(),
            'value' => $area->value,
        ], DevelopmentTaskArea::cases());
    }

    public function store(
        StoreDevelopmentTaskRequest $request,
        SubmitDevelopmentTask $submitDevelopmentTask,
    ): RedirectResponse {
        $task = $submitDevelopmentTask->handle(
            $request->toDto(),
            $this->authenticatedUser($request),
        );

        return to_route('development.tasks.show', $task)
            ->with('success', 'Development request submitted.');
    }

    public function show(Request $request, DevelopmentTask $developmentTask): Response
    {
        $this->authorize('view', $developmentTask);
        $developmentTask->load([
            'submitter',
            'reviewer',
            'assignee',
            'links' => fn (Relation $query): Relation => $query->with('addedBy')->latest()->orderByDesc('id'),
            'notes' => fn (Relation $query): Relation => $query->with('author')->oldest()->orderBy('id'),
        ]);
        $user = $this->authenticatedUser($request);

        return Inertia::render('Development/Tasks/Show', [
            'task' => DevelopmentTaskResource::make($developmentTask)->resolve($request),
            'assignees' => $user->can('manageRoadmap', $developmentTask)
                ? User::query()
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->get(['uuid', 'name'])
                    ->map(fn (User $user): array => [
                        'label' => $user->name,
                        'value' => $user->uuid,
                    ])
                    ->values()
                    ->all()
                : [],
            'priorities' => array_map(fn (TaskPriority $priority): array => [
                'label' => $priority->label(),
                'value' => $priority->value,
            ], TaskPriority::cases()),
            'linkTypes' => array_map(fn (DevelopmentTaskLinkType $type): array => [
                'label' => $type->label(),
                'value' => $type->value,
            ], DevelopmentTaskLinkType::cases()),
        ]);
    }
}
