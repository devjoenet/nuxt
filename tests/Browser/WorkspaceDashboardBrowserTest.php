<?php

declare(strict_types=1);

use App\Models\Lead;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

test('contributed CRM dashboard shows display labels across themes and mobile widths', function (): void {
    app(RolePermissionSeeder::class)->run();
    $user = User::factory()->create();
    $user->givePermissionTo('crm.view');
    Lead::factory()->create(['owner_id' => $user->id, 'status' => 'qualified']);
    Lead::factory()->create(['owner_id' => $user->id, 'status' => 'new']);
    $this->actingAs($user);
    foreach ([false, true] as $dark) {
        $page = visit('/dashboard');
        if ($dark) {
            $page->inDarkMode();
        }
        $page->assertSee('Recent leads')->assertSee('Qualified')->assertSee('New');
        foreach ([390, 1440] as $width) {
            $page->resize($width, 900)->assertScript('document.documentElement.scrollWidth <= window.innerWidth', true);
        }
        $page->click('Open CRM')->assertPathIs('/crm/leads')->assertNoJavaScriptErrors();
    }
});
