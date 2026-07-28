<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\FollowUpTaskStatus;
use App\Enums\LeadStatus;
use App\Enums\Permission;
use App\Models\FollowUpTask;
use App\Models\Lead;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $this->authenticatedUser($request);
        $canViewCrm = $user->can(Permission::CrmView->value);

        return Inertia::render('Dashboard', [
            'canViewCrm' => $canViewCrm,
            'stats' => $canViewCrm ? [
                ['label' => 'Open leads', 'value' => Lead::query()->visibleTo($user)->count(), 'icon' => 'i-ph-users-three'],
                ['label' => 'Qualified leads', 'value' => Lead::query()->visibleTo($user)->where('status', LeadStatus::Qualified)->count(), 'icon' => 'i-ph-funnel'],
                ['label' => 'Follow-ups due', 'value' => FollowUpTask::query()->visibleTo($user)->overdue()->count(), 'icon' => 'i-ph-warning-circle'],
                ['label' => 'Due soon', 'value' => FollowUpTask::query()->visibleTo($user)->where('status', FollowUpTaskStatus::Open)->dueSoon()->count(), 'icon' => 'i-ph-calendar-check'],
            ] : [],
            'recentLeads' => $canViewCrm ? Lead::query()->visibleTo($user)
                ->with(['contact', 'organization'])
                ->latest()
                ->limit(5)
                ->get()
                ->map(fn (Lead $lead): array => [
                    'uuid' => $lead->uuid,
                    'name' => $lead->contact->isVisibleTo($user) ? $lead->contact->name : 'Unknown contact',
                    'organization' => $lead->organization !== null && $lead->organization->isVisibleTo($user)
                        ? $lead->organization->name
                        : null,
                    'status' => $lead->status->value,
                    'created_at' => $lead->created_at->toISOString(),
                ]) : [],
            'upcomingTasks' => $canViewCrm ? FollowUpTask::query()->visibleTo($user)
                ->with('lead.contact')
                ->where('status', FollowUpTaskStatus::Open)
                ->orderBy('due_at')
                ->limit(5)
                ->get()
                ->map(function (FollowUpTask $task): array {
                    $lead = $task->lead;

                    return [
                        'uuid' => $task->uuid,
                        'title' => $task->title,
                        'lead_uuid' => $lead->uuid,
                        'lead_name' => $lead->contact->name,
                        'due_at' => $task->due_at->toISOString(),
                        'is_overdue' => $task->due_at->isPast(),
                    ];
                }) : [],
        ]);
    }
}
