<?php

declare(strict_types=1);

namespace App\Audit\Presenters;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use InvalidArgumentException;
use LogicException;
use SoutheastCode\Audit\Contracts\ActivityPresentation;
use SoutheastCode\Audit\Contracts\ActivityPresenter;
use SoutheastCode\Audit\Support\AuditMorphs;
use SoutheastCode\Crm\Contacts\Models\Contact;
use SoutheastCode\Crm\FollowUps\Models\FollowUpTask;
use SoutheastCode\Crm\Leads\Models\Lead;
use SoutheastCode\Crm\Organizations\Models\Organization;
use SoutheastCode\Crm\Shared\Models\CrmActivity;
use SoutheastCode\Crm\Shared\Models\Note;
use SoutheastCode\Modules\WorkspaceUser;
use Spatie\Activitylog\Models\Activity;

final class CrmActivityPresenter implements ActivityPresenter
{
    /** @return list<class-string<Lead|Contact|Organization|FollowUpTask|Note|CrmActivity>> */
    public function models(): array
    {
        return [Lead::class, Contact::class, Organization::class, FollowUpTask::class, Note::class, CrmActivity::class];
    }

    public function constrain(Builder $query, WorkspaceUser $user): void
    {
        $query->whereRaw('1 = 0');
        if (! $user->workspaceCan('crm.view')) {
            return;
        }

        if (! $user instanceof User) {
            throw new LogicException('CRM requires a configured Eloquent workspace user.');
        }

        foreach ($this->models() as $type) {
            $query->orWhere(fn (Builder $branch): Builder => $branch
                ->where('subject_type', (new $type)->getMorphClass())
                ->whereIn('subject_id', $this->visibleRecords($type, $user)->select('id')));
        }
    }

    public function eagerLoads(): array
    {
        return [Lead::class => ['contact'], FollowUpTask::class => ['lead.contact'], CrmActivity::class => ['lead.contact'], Note::class => ['subject.contact']];
    }

    public function present(Activity $activity, WorkspaceUser $user): ActivityPresentation
    {
        $record = $activity->subject;
        $label = AuditMorphs::label($activity->subject_type);
        if ($record === null) {
            return new ActivityPresentation($label.' #'.$activity->subject_id.' (unavailable)');
        }

        if ($record instanceof Lead || $record instanceof Contact || $record instanceof Organization) {
            $prefix = match (true) {
                $record instanceof Lead => 'crm.leads',
                $record instanceof Contact => 'crm.contacts',
                default => 'crm.organizations',
            };
            $label .= ' · '.($record instanceof Lead ? $record->contact->name : $record->name);

            return new ActivityPresentation($label, route("{$prefix}.history", $record));
        }

        if (! $record instanceof FollowUpTask && ! $record instanceof Note && ! $record instanceof CrmActivity) {
            throw new InvalidArgumentException('Unsupported CRM activity subject.');
        }

        $lead = $record instanceof Note ? $record->subject : $record->lead;
        $label .= ' · '.($record instanceof FollowUpTask ? $record->title : ($lead instanceof Lead ? $lead->contact->name : 'Record'));

        return new ActivityPresentation($label, $lead instanceof Lead ? route('crm.leads.history', $lead) : null);
    }

    /** @param class-string<Lead|Contact|Organization|FollowUpTask|Note|CrmActivity> $type
     * @return Builder<Lead>|Builder<Contact>|Builder<Organization>|Builder<FollowUpTask>|Builder<Note>|Builder<CrmActivity>
     */
    private function visibleRecords(string $type, User $user): Builder
    {
        if ($type === Note::class) {
            return Note::query()->where('subject_type', (new Lead)->getMorphClass())
                ->whereIn('subject_id', Lead::query()->visibleTo($user)->select('id'));
        }

        if ($type === CrmActivity::class) {
            return CrmActivity::query()->whereIn('lead_id', Lead::query()->visibleTo($user)->select('id'));
        }

        return match ($type) {
            Lead::class => Lead::withTrashed()->visibleTo($user),
            Contact::class => Contact::withTrashed()->visibleTo($user),
            Organization::class => Organization::withTrashed()->visibleTo($user),
            FollowUpTask::class => FollowUpTask::withTrashed()->visibleTo($user),
            default => throw new InvalidArgumentException('Unsupported recent-change record type.'),
        };
    }
}
