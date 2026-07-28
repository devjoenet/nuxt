<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\UserTestingPageData;
use App\Models\User;
use App\Models\UserTestingPage;
use Illuminate\Support\Facades\DB;

class UpdateUserTestingPage
{
    public function handle(UserTestingPage $page, UserTestingPageData $data, User $actor): UserTestingPage
    {
        return DB::transaction(function () use ($page, $data, $actor): UserTestingPage {
            $page = UserTestingPage::query()
                ->whereKey($page->getKey())
                ->lockForUpdate()
                ->firstOrFail();
            $tested = $data->tested ?? $page->tested_at !== null;
            $approved = $data->approved ?? $page->approved_at !== null;
            $roadmapTask = $data->roadmapTask ?? $page->roadmap_task_at !== null;

            if ($approved) {
                $tested = true;
            }

            $page->update([
                'tested_by' => $tested ? ($page->tested_at === null ? $actor->getKey() : $page->tested_by) : null,
                'tested_at' => $tested ? ($page->tested_at ?? now()) : null,
                'approved_at' => $approved ? ($page->approved_at ?? now()) : null,
                'roadmap_task_at' => $roadmapTask ? ($page->roadmap_task_at ?? now()) : null,
                'notes' => $data->notes,
            ]);

            return $page->refresh();
        });
    }
}
