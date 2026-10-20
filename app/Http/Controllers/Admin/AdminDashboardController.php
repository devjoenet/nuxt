<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FollowUpTask;
use App\Models\Lead;
use App\Models\User;
use App\Queries\RecentAdminChangesQuery;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class AdminDashboardController extends Controller
{
    public function __invoke(Request $request, RecentAdminChangesQuery $changes): Response
    {
        $user = $this->authenticatedUser($request);
        abort_unless($user->can('admin.dashboard.view'), 403);

        $stats = [];
        if ($user->can('jobs.view')) {
            $stats[] = $this->stat('Failed jobs', fn (): int => DB::connection(config('queue.failed.database'))->table(config()->string('queue.failed.table'))->count(), route('admin.jobs.index'));
        }

        if ($user->can('crm.view')) {
            $stats[] = $this->stat('Overdue follow-ups', fn (): int => FollowUpTask::query()->visibleTo($user)->whereHas('lead', fn (Builder $query): Builder => $query->whereNull('archived_at'))->overdue()->count(), route('crm.leads.index', ['overdue' => 1]));
            $stats[] = $this->stat('Unassigned leads', fn (): int => Lead::query()->visibleTo($user)->whereNull('archived_at')->whereNull('owner_id')->count(), route('crm.leads.index', ['unassigned' => 1]));
        }

        if ($user->can('admin.users.manage')) {
            $stats[] = $this->stat('Pending invitations', fn (): ?int => User::query()->untrackedInvitation()->exists() ? null : User::query()->pendingInvitation()->count(), route('admin.users.index', ['status' => 'pending']));
            $stats[] = $this->stat('Active users', fn (): int => User::query()->where('is_active', true)->count(), route('admin.users.index', ['status' => 'active']), attention: false);
        }

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
            'recentChanges' => $user->can('audit.view') ? $changes->forUser($user) : [],
            'canViewChanges' => $user->can('audit.view'),
        ]);
    }

    /** @return array{label: string, value: ?int, url: string, attention: bool} */
    private function stat(string $label, Closure $count, string $url, bool $attention = true): array
    {
        try {
            $value = $count();
        } catch (Throwable $throwable) {
            report($throwable);
            $value = null;
        }

        return ['label' => $label, 'value' => $value, 'url' => $url, 'attention' => $attention];
    }
}
