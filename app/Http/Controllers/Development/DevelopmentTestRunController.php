<?php

declare(strict_types=1);

namespace App\Http\Controllers\Development;

use App\Actions\RecordDevelopmentTestResult;
use App\Actions\RequestDevelopmentTaskTesting;
use App\Enums\DevelopmentTestRunStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Development\RecordDevelopmentTestResultRequest;
use App\Http\Requests\Development\RequestDevelopmentTaskTestingRequest;
use App\Http\Resources\DevelopmentTestRunResource;
use App\Models\DevelopmentTask;
use App\Models\DevelopmentTestRun;
use App\Queries\DevelopmentTestRunQuery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DevelopmentTestRunController extends Controller
{
    public function index(Request $request, DevelopmentTestRunQuery $query): Response
    {
        $this->authorize('viewAny', DevelopmentTestRun::class);
        $user = $this->authenticatedUser($request);

        return Inertia::render('Development/UserTesting/Index', [
            'testRuns' => DevelopmentTestRunResource::collection(
                $query->pendingFor($user)->get(),
            )->resolve($request),
        ]);
    }

    public function store(
        RequestDevelopmentTaskTestingRequest $request,
        DevelopmentTask $developmentTask,
        RequestDevelopmentTaskTesting $requestDevelopmentTaskTesting,
    ): RedirectResponse {
        $requestDevelopmentTaskTesting->handle(
            $developmentTask,
            $request->toDto(),
            $this->authenticatedUser($request),
        );

        return back()->with('success', 'User testing requested.');
    }

    public function update(
        RecordDevelopmentTestResultRequest $request,
        DevelopmentTestRun $developmentTestRun,
        RecordDevelopmentTestResult $recordDevelopmentTestResult,
    ): RedirectResponse {
        $testRun = $recordDevelopmentTestResult->handle(
            $developmentTestRun,
            $request->toDto(),
            $this->authenticatedUser($request),
        );

        if ($testRun->status === DevelopmentTestRunStatus::Accepted) {
            return to_route('development.userTesting.index')
                ->with('success', 'Testing accepted. The task is complete and archived.');
        }

        return to_route('development.tasks.show', $testRun->task)
            ->with('success', 'Changes requested. The task is ready for another implementation pass.');
    }
}
