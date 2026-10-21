<?php

declare(strict_types=1);

namespace App\Audit;

use App\Modules\WorkspaceUser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;

interface ActivityPresenter
{
    /** @return list<class-string<Model>> */
    public function models(): array;

    /** Add visibility predicates inside a grouped OR branch; never limit/order results here.
     * @param  Builder<Activity>  $query
     */
    public function constrain(Builder $query, WorkspaceUser $user): void;

    /** @return array<class-string<Model>, list<string>> */
    public function eagerLoads(): array;

    public function present(Activity $activity, WorkspaceUser $user): ActivityPresentation;
}
