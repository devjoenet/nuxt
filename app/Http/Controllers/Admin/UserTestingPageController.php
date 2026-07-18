<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUserTestingPageRequest;
use App\Models\Contact;
use App\Models\Lead;
use App\Models\Organization;
use App\Models\User;
use App\Models\UserTestingPage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserTestingPageController extends Controller
{
    public function index(Request $request): Response
    {
        $records = $this->firstAvailableRecords($request->user());

        return Inertia::render('Admin/UserTesting/Index', [
            'pages' => UserTestingPage::query()
                ->whereNull('approved_at')
                ->with('testedBy:id,name')
                ->orderBy('sort_order')
                ->get()
                ->map(fn (UserTestingPage $page): array => $this->pagePayload($page, $records))
                ->values(),
        ]);
    }

    /**
     * @param  array{lead: ?Lead, organization: ?Organization, contact: ?Contact}  $records
     * @return array<string, mixed>
     */
    private function pagePayload(UserTestingPage $page, array $records): array
    {
        return [
            'id' => $page->getKey(),
            'slug' => $page->slug,
            'category' => $page->category,
            'title' => $page->title,
            'roadmap_task_number' => config('user-testing.roadmap_tasks.'.$page->slug),
            'route_name' => $page->route_name,
            'path' => $page->path,
            'url' => $this->pageUrl($page, $records),
            'tested' => $page->tested_at !== null,
            'tested_at' => $page->tested_at?->toIso8601String(),
            'tested_by' => $page->testedBy?->name,
            'approved' => $page->approved_at !== null,
            'approved_at' => $page->approved_at?->toIso8601String(),
            'roadmap_task' => $page->roadmap_task_at !== null,
            'roadmap_task_at' => $page->roadmap_task_at?->toIso8601String(),
            'notes' => $page->notes,
            'updated_at' => $page->updated_at?->toIso8601String(),
        ];
    }

    public function update(UpdateUserTestingPageRequest $request, UserTestingPage $userTestingPage): RedirectResponse
    {
        $attributes = $request->validated();
        $tested = (bool) ($attributes['tested'] ?? false);
        $approved = (bool) ($attributes['approved'] ?? false);
        $roadmapTask = (bool) ($attributes['roadmap_task'] ?? false);

        if ($approved) {
            $tested = true;
        }

        $userTestingPage->update([
            'tested_by' => $tested ? $request->user()?->getKey() : null,
            'tested_at' => $tested ? now() : null,
            'approved_at' => $approved ? now() : null,
            'roadmap_task_at' => $roadmapTask ? now() : null,
            'notes' => filled($attributes['notes'] ?? null) ? mb_trim($attributes['notes']) : null,
        ]);

        return to_route('admin.userTesting.index')->with('success', 'Testing notes saved.');
    }

    public function updates(Request $request): JsonResponse
    {
        $request->validate(['since' => ['nullable', 'date']]);
        $since = $request->date('since');
        $records = $this->firstAvailableRecords($request->user());

        $pages = UserTestingPage::query()
            ->whereNull('approved_at')
            ->with('testedBy:id,name')
            ->when($since, function (Builder $query) use ($since): void {
                $query->where('updated_at', '>', $since);
            })
            ->orderBy('updated_at')
            ->get()
            ->map(fn (UserTestingPage $page): array => $this->pagePayload($page, $records))
            ->values();

        return response()->json([
            'pages' => $pages,
            'checked_at' => now()->toIso8601String(),
        ]);
    }

    /**
     * @return array{lead: ?Lead, organization: ?Organization, contact: ?Contact}
     */
    private function firstAvailableRecords(User $user): array
    {
        return [
            'lead' => Lead::query()->visibleTo($user)->oldest('created_at')->first(),
            'organization' => Organization::query()->visibleTo($user)->oldest('created_at')->first(),
            'contact' => Contact::query()->visibleTo($user)->oldest('created_at')->first(),
        ];
    }

    /**
     * @param  array{lead: ?Lead, organization: ?Organization, contact: ?Contact}  $records
     */
    private function pageUrl(UserTestingPage $page, array $records): ?string
    {
        return match ($page->route_name) {
            'crm.leads.show' => $this->modelRoute('crm.leads.show', 'lead', $records['lead']),
            'crm.leads.edit' => $this->modelRoute('crm.leads.edit', 'lead', $records['lead']),
            'crm.organizations.show' => $this->modelRoute('crm.organizations.show', 'organization', $records['organization']),
            'crm.organizations.edit' => $this->modelRoute('crm.organizations.edit', 'organization', $records['organization']),
            'crm.contacts.show' => $this->modelRoute('crm.contacts.show', 'contact', $records['contact']),
            'crm.contacts.edit' => $this->modelRoute('crm.contacts.edit', 'contact', $records['contact']),
            'home',
            'privacy',
            'login',
            'password.request',
            'verification.notice',
            'dashboard',
            'crm.leads.index',
            'crm.leads.create',
            'crm.organizations.index',
            'crm.organizations.create',
            'crm.contacts.index',
            'crm.contacts.create',
            'admin.dashboard',
            'admin.users.index',
            'admin.roles.index',
            'admin.jobs.index',
            'admin.settings.edit',
            'admin.audit.index',
            'profile.edit' => route($page->route_name, [], false),
            'security.show',
            'user-password.edit' => route('security.show', [], false),
            default => null,
        };
    }

    private function modelRoute(string $routeName, string $parameter, ?Model $model): ?string
    {
        return $model === null ? null : route($routeName, [$parameter => $model], false);
    }
}
