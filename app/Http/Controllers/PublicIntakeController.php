<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\CreateLeadFromIntake;
use App\Data\IntakeData;
use App\Http\Requests\StorePublicIntakeRequest;
use Illuminate\Http\RedirectResponse;

use function to_route;

class PublicIntakeController extends Controller
{
    public function __invoke(StorePublicIntakeRequest $request, CreateLeadFromIntake $createLead): RedirectResponse
    {
        $createLead->handle(IntakeData::fromRequest($request));

        return to_route('home')->with('intakeSuccess', true);
    }
}
