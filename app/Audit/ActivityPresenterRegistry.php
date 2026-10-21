<?php

declare(strict_types=1);

namespace App\Audit;

use App\Modules\ModuleRegistry;
use App\Modules\WorkspaceUser;
use App\Support\AuditMorphs;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use LogicException;
use Spatie\Activitylog\Models\Activity;

final class ActivityPresenterRegistry
{
    public function __construct(private ModuleRegistry $modules) {}

    /** @return array<class-string<Model>, ActivityPresenter> */
    public function presenters(bool $adminOverview = false): array
    {
        $presenters = [];
        foreach ($this->modules->all() as $module) {
            foreach ($module->extensions[$adminOverview ? 'audit.admin-presenters' : 'audit.presenters'] ?? [] as $class) {
                $presenter = app($class);
                if (! $presenter instanceof ActivityPresenter) {
                    throw new LogicException('Activity presenters must implement the Audit contract.');
                }

                foreach ($presenter->models() as $model) {
                    if (isset($presenters[$model])) {
                        throw new LogicException('Conflicting activity presenter: '.$model);
                    }

                    $presenters[$model] = $presenter;
                }
            }
        }

        return $presenters;
    }

    /** @param Builder<Activity> $query */
    public function constrain(Builder $query, WorkspaceUser $user): void
    {
        $query->where(function (Builder $query) use ($user): void {
            $query->whereRaw('1 = 0');
            foreach (array_unique($this->presenters(adminOverview: true), SORT_REGULAR) as $presenter) {
                $query->orWhere(fn (Builder $branch) => $presenter->constrain($branch, $user));
            }
        });
    }

    /** @return array<class-string<Model>, list<string>> */
    public function eagerLoads(): array
    {
        $loads = [];
        foreach ($this->presenters() as $presenter) {
            $loads = [...$loads, ...$presenter->eagerLoads()];
        }

        return $loads;
    }

    public function present(Activity $activity, WorkspaceUser $user): ActivityPresentation
    {
        $model = AuditMorphs::model($activity->subject_type);
        $presenter = $model === null ? null : ($this->presenters()[$model] ?? null);

        return $presenter?->present($activity, $user) ?? new ActivityPresentation('Unavailable record #'.$activity->subject_id.' (unavailable)');
    }
}
