<?php

declare(strict_types=1);

namespace App\Queries;

use App\Audit\ActivityPresenterRegistry;
use App\Modules\WorkspaceUser;
use App\Support\AuditMorphs;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Activity;

final class RecentAdminChangesQuery
{
    public function __construct(private ActivityPresenterRegistry $presenters) {}

    /** @return Collection<int, array{id: int, actor: string, action: string, record: string, url: ?string, created_at: ?string}> */
    public function forUser(WorkspaceUser $user): Collection
    {
        $query = Activity::query()->afterQuery(fn (mixed $activities): mixed => $activities instanceof EloquentCollection ? AuditMorphs::load($activities, ['causer', 'subject']) : $activities);
        $this->presenters->constrain($query, $user);

        return $query->orderByDesc('created_at')->orderByDesc('id')->limit(8)->get()
            ->loadMorph('subject', $this->presenters->eagerLoads())
            ->map(function (Activity $activity) use ($user): array {
                $presentation = $this->presenters->present($activity, $user);

                return [
                    'id' => $activity->id,
                    'actor' => $activity->causer instanceof WorkspaceUser ? $activity->causer->workspaceName() : ($activity->causer_id === null ? 'System' : 'Unavailable actor'),
                    'action' => Str::headline($activity->event ?? $activity->description),
                    'record' => $presentation->label, 'url' => $presentation->url,
                    'created_at' => $activity->created_at?->toISOString(),
                ];
            });
    }
}
