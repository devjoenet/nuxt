<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\RoleName;
use App\Models\User;
use Illuminate\Database\Seeder;
use SoutheastCode\Crm\Shared\Database\Seeders\CrmDemoSeeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RolePermissionSeeder::class);
        $this->call(\SoutheastCode\DevelopmentWorkflow\Database\Seeders\UserTestingPageSeeder::class);
        $this->call(CrmDemoSeeder::class);

        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $user->assignRole(RoleName::SuperAdmin->value);
    }
}
