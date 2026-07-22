<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class DevelopmentRoadmapController extends Controller
{
    public function __invoke(): Response
    {
        $roadmap = File::get(base_path('docs/development/active-roadmap.md'));

        return Inertia::render('Admin/Roadmap/Index', [
            'content' => Str::markdown($roadmap),
            'source' => 'docs/development/active-roadmap.md',
        ]);
    }
}
