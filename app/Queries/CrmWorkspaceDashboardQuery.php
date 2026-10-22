<?php

declare(strict_types=1);

namespace App\Queries;

use App\Enums\Permission;
use App\Models\User;
use Illuminate\Support\Str;
use LogicException;
use SoutheastCode\Crm\FollowUps\Enums\FollowUpTaskStatus;
use SoutheastCode\Crm\FollowUps\Models\FollowUpTask;
use SoutheastCode\Crm\Leads\Enums\LeadStatus;
use SoutheastCode\Crm\Leads\Models\Lead;
use SoutheastCode\Modules\WorkspaceDashboardContribution;
use SoutheastCode\Modules\WorkspaceUser;

final class CrmWorkspaceDashboardQuery implements WorkspaceDashboardContribution
{
    /** @return array<string, mixed> */
    public function props(WorkspaceUser $user): array
    {
        if (! $user instanceof User) {
            throw new LogicException('CRM requires a configured Eloquent workspace user.');
        }

        $canViewCrm = $user->workspaceCan(Permission::CrmView->value);

        return [
            'dashboardWidgets' => ['crm'],
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
                    'status_label' => Str::headline($lead->status->value),
                    'created_at' => $lead->created_at->toISOString(),
                ]) : [],
            'upcomingTasks' => $canViewCrm ? FollowUpTask::query()->visibleTo($user)
                ->with('lead.contact')
                ->where('status', FollowUpTaskStatus::Open)
                ->oldest('due_at')
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
        ];
    }
}
