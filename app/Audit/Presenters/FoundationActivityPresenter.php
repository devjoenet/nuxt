<?php

declare(strict_types=1);

namespace App\Audit\Presenters;

use App\Models\BusinessSetting;
use Illuminate\Database\Eloquent\Builder;
use SoutheastCode\Audit\Contracts\ActivityPresentation;
use SoutheastCode\Audit\Contracts\ActivityPresenter;
use SoutheastCode\Modules\WorkspaceUser;
use Spatie\Activitylog\Models\Activity;

final class FoundationActivityPresenter implements ActivityPresenter
{
    public function models(): array
    {
        return [BusinessSetting::class];
    }

    public function constrain(Builder $query, WorkspaceUser $user): void
    {
        $query->whereIn('subject_type', $user->workspaceCan('settings.manage') ? [(new BusinessSetting)->getMorphClass()] : []);
    }

    public function eagerLoads(): array
    {
        return [];
    }

    public function present(Activity $activity, WorkspaceUser $user): ActivityPresentation
    {
        return new ActivityPresentation($activity->subject === null ? 'Business Setting #'.$activity->subject_id.' (unavailable)' : 'Business settings', $activity->subject === null ? null : route('admin.audit.index', ['subject' => [$activity->subject_type]]));
    }
}
