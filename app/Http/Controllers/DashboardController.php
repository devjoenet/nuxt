<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use SoutheastCode\Modules\WorkspaceComposition;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        return Inertia::render('Dashboard', app(WorkspaceComposition::class)->dashboard($this->authenticatedUser($request)));
    }
}
