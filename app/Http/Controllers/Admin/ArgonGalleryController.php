<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Queries\ArgonGalleryManifestQuery;
use Inertia\Inertia;
use Inertia\Response;

class ArgonGalleryController extends Controller
{
    public function index(ArgonGalleryManifestQuery $manifest): Response
    {
        return Inertia::render('Admin/ArgonGallery/Index', $manifest->get());
    }

    public function show(string $example, ArgonGalleryManifestQuery $manifest): Response
    {
        $record = $manifest->find($example);

        abort_if($record === null, 404);

        return Inertia::render('Admin/ArgonGallery/Show', [
            'example' => $record,
        ]);
    }
}
