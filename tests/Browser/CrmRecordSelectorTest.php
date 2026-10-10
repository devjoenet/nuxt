<?php

declare(strict_types=1);

use App\Enums\RoleName;
use App\Models\Contact;
use App\Models\Organization;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

test('CRM lead selectors search and select records from the keyboard', function (): void {
    app(RolePermissionSeeder::class)->run();

    $admin = User::factory()->create();
    $admin->assignRole(RoleName::Admin->value);
    $contact = Contact::factory()->create([
        'name' => 'Task Twenty Four Contact',
        'email' => 'task-twenty-four@example.com',
        'owner_id' => $admin->id,
    ]);
    $organization = Organization::factory()->create([
        'name' => 'Task Twenty Four Organization',
        'owner_id' => $admin->id,
    ]);

    $this->actingAs($admin);

    $page = visit(route('crm.leads.create', absolute: false));

    $page->page()->locator('#lead-contact')->click();
    $page->assertSee("{$contact->name} — {$contact->email}");
    $page->screenshot(filename: 'lead-select-open');
    $page->page()->locator('[data-slot="input"] input[aria-expanded="true"]')->fill('T');
    $page->assertSee("{$contact->name} — {$contact->email}");
    $page->page()->locator('[data-slot="input"] input[aria-expanded="true"]')->press('ArrowDown');
    $page->page()->locator('[data-slot="input"] input[aria-expanded="true"]')->press('Enter');
    $page->assertValue('input[name="contact_id"]', $contact->uuid)
        ->assertAttribute('#lead-contact', 'aria-expanded', 'false');

    $page->page()->locator('#lead-organization')->click();
    $page->assertSee($organization->name);
    $page->page()->locator('[data-slot="input"] input[aria-expanded="true"]')->fill('T');
    $page->assertSee($organization->name);
    $page->page()->getByRole('option', ['name' => $organization->name, 'exact' => false])->click();

    $page
        ->assertValue('input[name="contact_id"]', $contact->uuid)
        ->assertValue('input[name="organization_id"]', $organization->uuid)
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs();

    $page->page()->locator('#lead-contact')->click();
    $page->page()->locator('[data-slot="input"] input[aria-expanded="true"]')->fill('No matching contact');
    $page->assertSee('No matching contacts.')->assertSee("{$contact->name} — {$contact->email}");
    $page->page()->locator('[data-slot="input"] input[aria-expanded="true"]')->fill('');
    $page->page()->getByRole('option', ['name' => "{$contact->name} — {$contact->email}", 'exact' => true])->press('Escape');
    $page->assertValue('input[name="contact_id"]', $contact->uuid)->assertNoJavaScriptErrors();

    foreach ([[1440, 900], [768, 1024], [390, 844]] as [$width, $height]) {
        $page->resize($width, $height)
            ->assertScript('document.documentElement.scrollWidth <= window.innerWidth', true)
            ->assertScript('(() => { const main = document.querySelector("[data-workspace-page-content]"); const footer = document.querySelector("[data-testid=workspace-footer]"); const action = main.querySelector("button[type=submit]"); return footer.getBoundingClientRect().top - action.getBoundingClientRect().bottom >= 16; })()', true);
        foreach (['light', 'dark'] as $theme) {
            $page->script("document.documentElement.classList.toggle('dark', '{$theme}' === 'dark')");
            $page->script('document.querySelector("[data-workspace-page-content] button[type=submit]").focus(); document.querySelector("[data-testid=workspace-footer]").scrollIntoView({ block: "end" })');
            $page->script('() => new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(resolve)))');
            $page->assertScript('document.querySelector("[data-workspace-page-content]").getBoundingClientRect().right <= window.innerWidth', true);
            $page->assertScript('document.querySelector("[data-workspace-page-content] button[type=submit]").getBoundingClientRect().right <= window.innerWidth', true);
            $page->screenshot(filename: "lead-spacing-{$width}-{$theme}");
        }
    }

    foreach (['dashboard', 'crm.contacts.index'] as $route) {
        $workspace = visit(route($route, absolute: false));
        foreach ([[1440, 900], [768, 1024], [390, 844]] as [$width, $height]) {
            $workspace->resize($width, $height)
                ->assertScript('document.documentElement.scrollWidth <= window.innerWidth', true)
                ->assertScript('(() => { const main = document.querySelector("[data-workspace-page-content]"); return parseFloat(getComputedStyle(main).paddingBottom) >= 16; })()', true)
                ->assertNoJavaScriptErrors();
        }
    }
});

test('CRM selectors load additional choices without replacing the first page', function (): void {
    app(RolePermissionSeeder::class)->run();
    $admin = User::factory()->create();
    $admin->assignRole(RoleName::Admin->value);
    Contact::factory()->count(20)->create(['name' => 'Alpha Contact', 'owner_id' => $admin->id]);
    $last = Contact::factory()->create(['name' => 'Zulu Contact', 'owner_id' => $admin->id]);
    $this->actingAs($admin);

    $page = visit(route('crm.leads.create', absolute: false));
    $page->page()->locator('#lead-contact')->click();
    $page->assertSee('Load more options')->click('Load more options')->assertSee('Zulu Contact');
    $page->page()->getByRole('option', ['name' => "{$last->name} — {$last->email}", 'exact' => true])->click();
    $page->assertValue('input[name="contact_id"]', $last->uuid)->assertNoJavaScriptErrors();
});

test('CRM selectors distinguish empty data and request failure and allow retry', function (): void {
    app(RolePermissionSeeder::class)->run();
    $admin = User::factory()->create();
    $admin->assignRole(RoleName::Admin->value);
    $this->actingAs($admin);

    $page = visit(route('crm.leads.create', absolute: false));
    $page->page()->locator('#lead-contact')->click();
    $page->assertSee('No contacts available.');
    $page->page()->locator('[data-slot="input"] input[aria-expanded="true"]')->press('Escape');
    $page->script('() => { const original = XMLHttpRequest.prototype.send; XMLHttpRequest.prototype.send = function () { XMLHttpRequest.prototype.send = original; this.dispatchEvent(new ProgressEvent("error")); }; }');
    $page->page()->locator('#lead-contact')->click();
    $page->assertSee('Options could not be loaded. Try again.')->click('Retry')->assertSee('No contacts available.')->assertDontSee('Retry')->assertNoJavaScriptErrors();
});

test('CRM selectors ignore stale responses even when cancellation does not stop delivery', function (): void {
    app(RolePermissionSeeder::class)->run();
    $admin = User::factory()->create();
    $admin->assignRole(RoleName::Admin->value);
    $contact = Contact::factory()->create(['name' => 'Zebra Contact', 'email' => 'zebra@example.com', 'owner_id' => $admin->id]);
    $this->actingAs($admin);

    $page = visit(route('crm.leads.create', absolute: false));
    $page->script(<<<'JS'
        () => {
            const original = XMLHttpRequest.prototype.send;
            XMLHttpRequest.prototype.send = function () {
                XMLHttpRequest.prototype.send = original;
                this.abort = () => {};
                window.deliverStaleOptions = () => {
                    Object.defineProperty(this, 'status', { value: 200 });
                    Object.defineProperty(this, 'responseText', { value: JSON.stringify({ data: [{ label: 'Stale Contact', value: 'stale' }], links: { next: null } }) });
                    this.onload(new ProgressEvent('load'));
                };
            };
        }
        JS);
    $page->page()->locator('#lead-contact')->click();
    $page->assertSee('Loading options…');
    $page->page()->locator('[data-slot="input"] input[aria-expanded="true"]')->fill('Z');
    $page->assertSee("{$contact->name} — {$contact->email}");
    $page->script('window.deliverStaleOptions()');
    $page->assertSee("{$contact->name} — {$contact->email}")->assertDontSee('Stale Contact')->assertNoJavaScriptErrors();
});
