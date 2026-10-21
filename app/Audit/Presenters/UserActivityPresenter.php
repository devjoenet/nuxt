<?php

declare(strict_types=1);

namespace App\Audit\Presenters;

use App\Audit\ActivityPresentation;
use App\Audit\ActivityPresenter;
use App\Models\User;
use App\Modules\WorkspaceUser;
use App\Support\AuditMorphs;
use App\Support\WorkspaceUsers;
use Illuminate\Database\Eloquent\Builder;
use LogicException;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

final class UserActivityPresenter implements ActivityPresenter
{
    public function models(): array
    {
        $role = app(PermissionRegistrar::class)->getRoleClass();
        if (! is_a($role, Role::class, true)) {
            throw new LogicException('The configured role must extend the Permission role model.');
        }

        return [WorkspaceUsers::model(), $role];
    }

    public function constrain(Builder $query, WorkspaceUser $user): void
    {
        $models = $this->models();
        $types = [];
        if ($user->workspaceCan('admin.users.manage')) {
            $types[] = (new $models[0])->getMorphClass();
        }

        if ($user->workspaceCan('admin.roles.manage')) {
            $types[] = (new $models[1])->getMorphClass();
        }

        $query->whereIn('subject_type', $types);
    }

    public function eagerLoads(): array
    {
        return [];
    }

    public function present(Activity $activity, WorkspaceUser $user): ActivityPresentation
    {
        $record = $activity->subject;
        if ($record !== null && ! $record instanceof User && ! $record instanceof Role) {
            throw new LogicException('Unsupported user-management subject.');
        }

        $label = AuditMorphs::label($activity->subject_type);

        return new ActivityPresentation($record === null ? $label.' #'.$activity->subject_id.' (unavailable)' : $label.' · '.$record->name, $record === null ? null : route('admin.audit.index', ['subject' => [$activity->subject_type]]));
    }
}
