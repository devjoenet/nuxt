<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\SubmitPublicIntake;
use App\Http\Requests\StorePublicIntakeRequest;
use Illuminate\Http\RedirectResponse;

use function to_route;

class PublicIntakeController extends Controller
{
    public function __invoke(StorePublicIntakeRequest $request, SubmitPublicIntake $submit): RedirectResponse
    {
        $submit->handle($request->validated(), $request->ip(), $request->userAgent());

        return to_route('home')->with('intakeSuccess', true);
    }
}
