<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Resources\UserResource;
use App\Modules\WorkspaceComposition;
use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Override;

use function config;
use function mb_trim;
use function str;

class HandleInertiaRequests extends Middleware
{
    #[Override]
    protected $rootView = 'app';

    /** @return array<string, mixed> */
    #[Override]
    public function share(Request $request): array
    {
        [$message, $author] = str(Inspiring::quotes()->random())->explode('-');

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'quote' => ['message' => mb_trim((string) $message), 'author' => mb_trim((string) $author)],
            'appearance' => match ($request->cookie('appearance')) {
                'dark' => 'dark',
                'light' => 'light',
                default => 'system',
            },
            'auth' => [
                'user' => $request->user() ? new UserResource($request->user())->resolve() : null,
            ],
            'flash' => [
                'intakeSuccess' => fn (): bool => $request->hasSession() && $request->session()->get('intakeSuccess', false),
                'success' => fn (): ?string => $request->hasSession() ? $request->session()->get('success') : null,
            ],
            'workspace' => fn (): array => $request->user() ? app(WorkspaceComposition::class)->navigation($request->user(), $request) : ['navigation' => [], 'breadcrumbs' => []],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }
}
