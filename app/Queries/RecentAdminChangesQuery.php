<?php

declare(strict_types=1);

namespace App\Queries;

use App\Models\BusinessSetting;
use App\Models\Contact;
use App\Models\CrmActivity;
use App\Models\FollowUpTask;
use App\Models\Lead;
use App\Models\Note;
use App\Models\Organization;
use App\Models\User;
use App\Support\WorkspaceUsers;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;

final class RecentAdminChangesQuery
{
    /** @return Collection<int, array{id: int, actor: string, action: string, record: string, url: ?string, created_at: ?string}> */
    public function forUser(User $user): Collection
    {
        $types = [];
        if ($user->can('admin.users.manage')) {
            $types[] = WorkspaceUsers::model();
        }

        if ($user->can('admin.roles.manage')) {
            $types[] = Role::class;
        }

        if ($user->can('settings.manage')) {
            $types[] = BusinessSetting::class;
        }

        $crmTypes = [Lead::class, Contact::class, Organization::class, FollowUpTask::class, Note::class, CrmActivity::class];

        return Activity::query()->with(['causer', 'subject'])
            ->where(function (Builder $query) use ($user, $types, $crmTypes): void {
                $query->whereIn('subject_type', array_map(fn (string $type): string => (new $type)->getMorphClass(), $types));
                if ($user->can('crm.view')) {
                    foreach ($crmTypes as $type) {
                        $query->orWhere(function (Builder $query) use ($type, $user): void {
                            $query->where('subject_type', (new $type)->getMorphClass())
                                ->whereIn('subject_id', $this->visibleRecords($type, $user)->select('id'));
                        });
                    }
                }
            })
            ->orderByDesc('created_at')->orderByDesc('id')->limit(8)->get()
            ->loadMorph('subject', [Lead::class => ['contact'], FollowUpTask::class => ['lead.contact'], CrmActivity::class => ['lead.contact'], Note::class => ['subject.contact']])
            ->map(function (Activity $activity): array {
                $record = $activity->subject;
                $label = Str::headline(class_basename($activity->subject_type ?? 'Record'));
                if ($record === null) {
                    $label .= ' #'.$activity->subject_id.' (unavailable)';
                }

                $url = null;
                if ($record instanceof Lead || $record instanceof Contact || $record instanceof Organization) {
                    $prefix = match (true) {
                        $record instanceof Lead => 'crm.leads',
                        $record instanceof Contact => 'crm.contacts',
                        default => 'crm.organizations',
                    };
                    $label .= ' · '.($record instanceof Lead ? $record->contact->name : $record->name);
                    $url = route("{$prefix}.history", $record);
                } elseif ($record instanceof FollowUpTask || $record instanceof CrmActivity || $record instanceof Note) {
                    $lead = $record instanceof Note ? $record->subject : $record->lead;
                    $label .= ' · '.($record instanceof FollowUpTask ? $record->title : ($lead instanceof Lead ? $lead->contact->name : 'Record'));
                    $url = $lead instanceof Lead ? route('crm.leads.history', $lead) : null;
                } elseif ($record instanceof User || $record instanceof Role) {
                    $label .= ' · '.$record->name;
                    $url = route('admin.audit.index', ['subject' => [$record->getMorphClass()]]);
                } elseif ($record instanceof BusinessSetting) {
                    $label = 'Business settings';
                    $url = route('admin.audit.index', ['subject' => [$record->getMorphClass()]]);
                }

                return [
                    'id' => $activity->id,
                    'actor' => $activity->causer instanceof User ? $activity->causer->name : ($activity->causer_id === null ? 'System' : 'Unavailable actor'),
                    'action' => Str::headline($activity->event ?? $activity->description),
                    'record' => $label,
                    'url' => $url,
                    'created_at' => $activity->created_at?->toISOString(),
                ];
            });
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
