<?php

declare(strict_types=1);

namespace App\Http\Controllers\Development;

use App\Actions\RequestDevelopmentTaskInformation;
use App\Http\Controllers\Controller;
use App\Http\Requests\Development\RequestDevelopmentTaskInformationRequest;
use App\Models\DevelopmentTask;
use Illuminate\Http\RedirectResponse;

class DevelopmentTaskInformationRequestController extends Controller
{
    public function __invoke(
        RequestDevelopmentTaskInformationRequest $request,
        DevelopmentTask $developmentTask,
        RequestDevelopmentTaskInformation $requestDevelopmentTaskInformation,
    ): RedirectResponse {
        $requestDevelopmentTaskInformation->handle(
            $developmentTask,
            $request->toDto(),
            $this->authenticatedUser($request),
        );

        return back()->with('success', 'Information requested.');
    }
}
