<?php

declare(strict_types=1);

namespace App\Queries;

use App\Models\FollowUpTask;
use App\Models\Lead;
use App\Models\User;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use LogicException;
use SoutheastCode\Modules\AdminSummaryContribution;
use SoutheastCode\Modules\WorkspaceUser;
use Throwable;

final class CrmAdminSummaryQuery implements AdminSummaryContribution
{
    /** @return list<array{label: string, value: ?int, url: string, attention: bool}> */
    public function stats(WorkspaceUser $user): array
    {
        if (! $user->workspaceCan('crm.view')) {
            return [];
        }

        if (! $user instanceof User) {
            throw new LogicException('CRM requires a configured Eloquent workspace user.');
        }

        return [
            $this->stat('Overdue follow-ups', fn (): int => FollowUpTask::query()->visibleTo($user)->whereHas('lead', fn (Builder $query): Builder => $query->whereNull('archived_at'))->overdue()->count(), route('crm.leads.index', ['overdue' => 1])),
            $this->stat('Unassigned leads', fn (): int => Lead::query()->visibleTo($user)->whereNull('archived_at')->whereNull('owner_id')->count(), route('crm.leads.index', ['unassigned' => 1])),
        ];
    }

    /** @return array{label: string, value: ?int, url: string, attention: bool} */
    private function stat(string $label, Closure $count, string $url): array
    {
        try {
            $value = $count();
        } catch (Throwable $throwable) {
            report($throwable);
            $value = null;
        }

        return ['label' => $label, 'value' => $value, 'url' => $url, 'attention' => true];
    }
}
