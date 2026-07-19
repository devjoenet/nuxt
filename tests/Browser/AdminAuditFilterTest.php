<?php

declare(strict_types=1);

use App\Models\Contact;
use App\Models\Lead;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

test('audit filters remain usable across themes and narrow widths', function (): void {
    app(RolePermissionSeeder::class)->run();

    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    $lead = Lead::factory()->create();
    $contact = Contact::factory()->create();

    activity()->causedBy($admin)->performedOn($lead)->event('created')->log('Browser audit record');
    activity()->causedBy($admin)->performedOn($contact)->event('updated')->log('Browser updated record');

    $this->actingAs($admin);

    $page = visit('/admin/audit')
        ->assertSee('Audit log')
        ->assertScript('getComputedStyle(document.querySelector(".audit-toolbar"))?.overflowY', 'visible')
        ->assertAttribute('#audit-event', 'aria-expanded', 'false')
        ->assertScript('document.querySelector("#audit-search")?.closest(".form-field__shell")?.querySelector("[data-slot=leading]")?.getBoundingClientRect().right < document.querySelector("#audit-search")?.closest(".form-field__shell")?.querySelector(".form-field__label")?.getBoundingClientRect().left', true)
        ->click('#audit-event')
        ->assertAttribute('#audit-event', 'aria-expanded', 'true')
        ->assertSee('Created')
        ->assertDontSee('An invalid event')
        ->click('Created')
        ->keys('#audit-event', 'Escape')
        ->click('#audit-event')
        ->click('Updated')
        ->keys('#audit-event', 'Escape')
        ->assertAttribute('#audit-event', 'aria-expanded', 'false')
        ->click('#audit-causer')
        ->assertAttribute('#audit-causer', 'aria-expanded', 'true')
        ->click($admin->name)
        ->keys('#audit-causer', 'Escape')
        ->click('#audit-subject')
        ->assertAttribute('#audit-subject', 'aria-expanded', 'true')
        ->click('Lead')
        ->pressAndWaitFor('Filter')
        ->assertSee('Browser audit record')
        ->assertSee('Created')
        ->assertScript('document.documentElement.scrollWidth <= window.innerWidth', true)
        ->resize(390, 844)
        ->assertScript('document.documentElement.scrollWidth <= window.innerWidth', true)
        ->assertScript('document.querySelector("#audit-subject")?.scrollWidth <= document.querySelector("#audit-subject")?.clientWidth', true)
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs();

    visit('/admin/audit')->inDarkMode()
        ->assertScript('document.documentElement.classList.contains("dark")', true)
        ->assertScript('getComputedStyle(document.querySelector(".audit-toolbar"))?.overflowY', 'visible')
        ->assertScript('document.querySelector("#audit-search")?.closest(".form-field__shell")?.querySelector("[data-slot=leading]")?.getBoundingClientRect().right < document.querySelector("#audit-search")?.closest(".form-field__shell")?.querySelector(".form-field__label")?.getBoundingClientRect().left', true)
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs();

    $page->navigate('/admin/audit?event%5B%5D=not-a-real-event')
        ->assertSee('No activity matches these filters.')
        ->assertNoJavaScriptErrors();
});
