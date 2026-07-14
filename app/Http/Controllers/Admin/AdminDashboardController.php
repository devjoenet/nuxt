<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\LeadStatus;
use App\Http\Controllers\Controller;
use App\Models\FollowUpTask;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Activitylog\Models\Activity;

class AdminDashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        abort_unless($request->user()?->can('admin.dashboard.view'), 403);

        $recentActivities = Activity::query()
            ->with('causer')
            ->latest()
            ->limit(8)
            ->get()
            ->map(function (mixed $activity): array {
                /** @var Activity $activity */
                $causer = $activity->causer;

                return [
                    'id' => $activity->id,
                    'description' => $activity->description,
                    'causer' => $causer instanceof User ? $causer->name : null,
                    'created_at' => $activity->created_at?->toISOString(),
                ];
            });

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                ['label' => 'All leads', 'value' => Lead::query()->count()],
                ['label' => 'Qualified pipeline', 'value' => Lead::query()->where('status', LeadStatus::Qualified)->count()],
                ['label' => 'Follow-ups due', 'value' => FollowUpTask::query()->overdue()->count()],
                ['label' => 'Failed jobs', 'value' => DB::table((string) config('queue.failed.table'))->count()],
                ['label' => 'Active users', 'value' => User::query()->where('is_active', true)->count()],
            ],
            'recentActivities' => $recentActivities,
            'recentUsers' => User::query()->with('roles')->latest()->limit(6)->get()->map(fn (User $user): array => [
                'id' => $user->getKey(),
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->getRoleNames()->first(),
                'is_active' => $user->is_active,
            ]),
        ]);
    }
}
