<?php

declare(strict_types=1);

use App\Models\Contact;
use App\Models\Lead;
use App\Models\Organization;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

test('operational tables use alternating row backgrounds in both color modes', function (): void {
    app(RolePermissionSeeder::class)->run();

    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    User::factory()->count(2)->create();
    Organization::factory()->count(3)->create();
    Contact::factory()->count(3)->create();
    Lead::factory()->count(3)->create();

    activity()->causedBy($admin)->event('table-stripe-one')->log('First table stripe record');
    activity()->causedBy($admin)->event('table-stripe-two')->log('Second table stripe record');

    $this->actingAs($admin);

    $rowsHaveAlternatingBackgrounds = <<<'JS'
        (() => {
            const rows = document.querySelectorAll('table tbody tr');

            return rows.length >= 2
                && getComputedStyle(rows[0]).backgroundColor !== getComputedStyle(rows[1]).backgroundColor;
        })()
        JS;

    $hoverChangesRowBackground = <<<'JS'
        (() => {
            const rows = document.querySelectorAll('table tbody tr');

            return rows.length >= 3
                && getComputedStyle(rows[0]).backgroundColor !== getComputedStyle(rows[2]).backgroundColor;
        })()
        JS;

    $darkStripeUsesAccentedBackground = <<<'JS'
        (() => {
            const rows = document.querySelectorAll('table tbody tr');
            const accentProbe = document.createElement('div');
            accentProbe.style.backgroundColor = 'var(--ui-bg-accented)';
            document.body.append(accentProbe);

            const accentBackground = getComputedStyle(accentProbe).backgroundColor;
            accentProbe.remove();

            return rows.length >= 2
                && getComputedStyle(rows[1]).backgroundColor === accentBackground;
        })()
        JS;

    $darkHoverUsesStrongerBackground = <<<'JS'
        (() => {
            const rows = document.querySelectorAll('table tbody tr');
            const hoverProbe = document.createElement('div');
            hoverProbe.style.backgroundColor = 'var(--color-slate-700)';
            document.body.append(hoverProbe);

            const hoverBackground = getComputedStyle(hoverProbe).backgroundColor;
            hoverProbe.remove();

            return rows.length >= 1
                && getComputedStyle(rows[0]).backgroundColor === hoverBackground;
        })()
        JS;

    $lightPage = visit(route('crm.organizations.index', absolute: false))
        ->assertSee('Organizations')
        ->assertScript($rowsHaveAlternatingBackgrounds, true)
        ->hover('table tbody tr:nth-child(1)')
        ->assertScript($hoverChangesRowBackground, true)
        ->navigate(route('crm.contacts.index', absolute: false))
        ->assertSee('Contacts')
        ->assertScript($rowsHaveAlternatingBackgrounds, true)
        ->hover('table tbody tr:nth-child(1)')
        ->assertScript($hoverChangesRowBackground, true)
        ->navigate(route('crm.leads.index', absolute: false))
        ->assertSee('CRM leads')
        ->assertScript($rowsHaveAlternatingBackgrounds, true)
        ->hover('table tbody tr:nth-child(1)')
        ->assertScript($hoverChangesRowBackground, true)
        ->navigate(route('admin.users.index', absolute: false))
        ->assertSee('Workspace users')
        ->assertScript($rowsHaveAlternatingBackgrounds, true)
        ->hover('table tbody tr:nth-child(1)')
        ->assertScript($hoverChangesRowBackground, true)
        ->navigate(route('admin.audit.index', absolute: false))
        ->assertSee('Audit log')
        ->assertScript($rowsHaveAlternatingBackgrounds, true)
        ->hover('table tbody tr:nth-child(1)')
        ->assertScript($hoverChangesRowBackground, true)
        ->resize(390, 844)
        ->assertScript('document.documentElement.scrollWidth <= window.innerWidth', true)
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs();

    $darkPage = visit(route('crm.organizations.index', absolute: false))
        ->inDarkMode()
        ->assertScript('document.documentElement.classList.contains("dark")', true)
        ->assertScript($rowsHaveAlternatingBackgrounds, true)
        ->assertScript($darkStripeUsesAccentedBackground, true)
        ->hover('table tbody tr:nth-child(1)')
        ->assertScript($hoverChangesRowBackground, true)
        ->assertScript($darkHoverUsesStrongerBackground, true)
        ->navigate(route('crm.contacts.index', absolute: false))
        ->assertScript($rowsHaveAlternatingBackgrounds, true)
        ->assertScript($darkStripeUsesAccentedBackground, true)
        ->hover('table tbody tr:nth-child(1)')
        ->assertScript($hoverChangesRowBackground, true)
        ->assertScript($darkHoverUsesStrongerBackground, true)
        ->navigate(route('crm.leads.index', absolute: false))
        ->assertScript($rowsHaveAlternatingBackgrounds, true)
        ->assertScript($darkStripeUsesAccentedBackground, true)
        ->hover('table tbody tr:nth-child(1)')
        ->assertScript($hoverChangesRowBackground, true)
        ->assertScript($darkHoverUsesStrongerBackground, true)
        ->navigate(route('admin.users.index', absolute: false))
        ->assertScript($rowsHaveAlternatingBackgrounds, true)
        ->assertScript($darkStripeUsesAccentedBackground, true)
        ->hover('table tbody tr:nth-child(1)')
        ->assertScript($hoverChangesRowBackground, true)
        ->assertScript($darkHoverUsesStrongerBackground, true)
        ->navigate(route('admin.audit.index', absolute: false))
        ->assertScript($rowsHaveAlternatingBackgrounds, true)
        ->assertScript($darkStripeUsesAccentedBackground, true)
        ->hover('table tbody tr:nth-child(1)')
        ->assertScript($hoverChangesRowBackground, true)
        ->assertScript($darkHoverUsesStrongerBackground, true)
        ->resize(390, 844)
        ->assertScript('document.documentElement.scrollWidth <= window.innerWidth', true)
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs();

    $lightPage->assertPathIs(route('admin.audit.index', absolute: false));
    $darkPage->assertPathIs(route('admin.audit.index', absolute: false));
});
