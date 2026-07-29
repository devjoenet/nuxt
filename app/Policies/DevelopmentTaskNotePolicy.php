<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\DevelopmentNoteAudience;
use App\Enums\Permission;
use App\Models\DevelopmentTask;
use App\Models\DevelopmentTaskNote;
use App\Models\User;

class DevelopmentTaskNotePolicy
{
    public function view(User $user, DevelopmentTaskNote $note): bool
    {
        if (! $user->can('view', $note->task)) {
            return false;
        }

        if ($note->audience === DevelopmentNoteAudience::Participants) {
            return true;
        }

        return $this->isDevelopmentTeamMember($user, $note->task);
    }

    private function isDevelopmentTeamMember(User $user, DevelopmentTask $task): bool
    {
        if ($task->assignee_id === $user->getKey()) {
            return true;
        }

        return $user->canAny([
            Permission::DevelopmentRequestsReview->value,
            Permission::DevelopmentRoadmapManage->value,
            Permission::DevelopmentUserTestingManage->value,
        ]);
    }

    public function create(
        User $user,
        DevelopmentTask $task,
        DevelopmentNoteAudience $audience,
    ): bool {
        if (! $user->can('view', $task)) {
            return false;
        }

        if ($audience === DevelopmentNoteAudience::Participants) {
            return true;
        }

        return $this->isDevelopmentTeamMember($user, $task);
    }
}
