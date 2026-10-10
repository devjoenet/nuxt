<?php

declare(strict_types=1);

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

test('admin overview shows empty attention and opens pending invitations', function (bool $dark): void {
    app(RolePermissionSeeder::class)->run();
    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    $this->actingAs($admin);
    $page = visit('/admin');
    if ($dark) {
        $page->inDarkMode();
    }
    $page->assertSee('Needs attention')
        ->assertSee('Nothing needs attention.')
        ->assertSee('Recent changes')
        ->assertNoJavaScriptErrors()
        ->click('Pending invitations')
        ->assertQueryStringHas('status', 'pending')
        ->assertSee('All users')
        ->assertNoJavaScriptErrors();
})->with([false, true]);
