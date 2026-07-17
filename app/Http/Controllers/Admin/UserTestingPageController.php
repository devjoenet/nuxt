<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUserTestingPageRequest;
use App\Models\UserTestingPage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserTestingPageController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/UserTesting/Index', [
            'pages' => UserTestingPage::query()
                ->with('testedBy:id,name')
                ->orderBy('sort_order')
                ->get()
                ->map(fn (UserTestingPage $page): array => $this->pagePayload($page))
                ->values(),
        ]);
    }

    /** @return array<string, mixed> */
    private function pagePayload(UserTestingPage $page): array
    {
        return [
            'id' => $page->getKey(),
            'slug' => $page->slug,
            'category' => $page->category,
            'title' => $page->title,
            'route_name' => $page->route_name,
            'path' => $page->path,
            'tested' => $page->tested_at !== null,
            'tested_at' => $page->tested_at?->toIso8601String(),
            'tested_by' => $page->testedBy?->name,
            'notes' => $page->notes,
            'updated_at' => $page->updated_at?->toIso8601String(),
        ];
    }

    public function update(UpdateUserTestingPageRequest $request, UserTestingPage $userTestingPage): RedirectResponse
    {
        $attributes = $request->validated();
        $tested = (bool) ($attributes['tested'] ?? false);

        $userTestingPage->update([
            'tested_by' => $tested ? $request->user()?->getKey() : null,
            'tested_at' => $tested ? now() : null,
            'notes' => filled($attributes['notes'] ?? null) ? mb_trim($attributes['notes']) : null,
        ]);

        return to_route('admin.userTesting.index')->with('success', 'Testing notes saved.');
    }

    public function updates(Request $request): JsonResponse
    {
        $request->validate(['since' => ['nullable', 'date']]);
        $since = $request->date('since');

        $pages = UserTestingPage::query()
            ->with('testedBy:id,name')
            ->when($since, function (Builder $query) use ($since): void {
                $query->where('updated_at', '>', $since);
            })
            ->orderBy('updated_at')
            ->get()
            ->map(fn (UserTestingPage $page): array => $this->pagePayload($page))
            ->values();

        return response()->json([
            'pages' => $pages,
            'checked_at' => now()->toIso8601String(),
        ]);
    }
}
