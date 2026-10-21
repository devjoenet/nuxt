<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Modules\WorkspaceComposition;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        return Inertia::render('Dashboard', app(WorkspaceComposition::class)->dashboard($this->authenticatedUser($request)));
    }
}
