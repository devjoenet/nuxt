<?php

declare(strict_types=1);

namespace App\Http\Controllers\Development;

use App\Actions\RejectDevelopmentTask;
use App\Http\Controllers\Controller;
use App\Http\Requests\Development\RejectDevelopmentTaskRequest;
use App\Models\DevelopmentTask;
use Illuminate\Http\RedirectResponse;

class DevelopmentTaskRejectionController extends Controller
{
    public function __invoke(
        RejectDevelopmentTaskRequest $request,
        DevelopmentTask $developmentTask,
        RejectDevelopmentTask $rejectDevelopmentTask,
    ): RedirectResponse {
        $rejectDevelopmentTask->handle(
            $developmentTask,
            $request->toDto(),
            $this->authenticatedUser($request),
        );

        return to_route('development.review.index')
            ->with('success', 'Development request rejected and archived.');
    }
}
