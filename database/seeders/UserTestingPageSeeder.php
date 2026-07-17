<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\UserTestingPage;
use Illuminate\Database\Seeder;

class UserTestingPageSeeder extends Seeder
{
    public function run(): void
    {
        foreach (config('user-testing.pages', []) as $page) {
            $record = UserTestingPage::query()->firstOrNew(['slug' => $page['slug']]);
            $isNew = ! $record->exists;

            $record->fill($page);

            if ($isNew) {
                $record->notes = $page['notes'] ?? null;
            }

            $record->save();
        }
    }
}
