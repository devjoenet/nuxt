<?php

declare(strict_types=1);

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function (): void {
    app(RolePermissionSeeder::class)->run();
});

test('every complex reference mounts in both themes without overflow or client errors', function (string $id, string $heading, string $theme): void {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    $this->actingAs($admin);

    $visit = visit(route('admin.argonGallery.examples.show', $id, absolute: false));
    $page = ($theme === 'dark' ? $visit->inDarkMode() : $visit->inLightMode())
        ->assertSee($heading)
        ->assertScript('document.documentElement.classList.contains("dark")', $theme === 'dark')
        ->resize(1440, 1000)
        ->assertScript('document.documentElement.scrollWidth <= window.innerWidth', true)
        ->assertScript('async () => (await window.axe.run(document.querySelector("section[aria-label$=\"reference implementation\"]"))).violations.filter(issue => ["critical", "serious"].includes(issue.impact))', [])
        ->screenshotElement('section[aria-label$="reference implementation"]', $id.'-'.$theme)
        ->resize(768, 1024)
        ->assertScript('document.documentElement.scrollWidth <= window.innerWidth', true)
        ->resize(390, 844)
        ->assertScript('document.documentElement.scrollWidth <= window.innerWidth', true)
        ->assertSee($heading)
        ->assertNoAccessibilityIssues()
        ->assertNoJavaScriptErrors();
})->with([
    ['applications-analytics', 'Traffic channels'],
    ['applications-calendar', 'Team calendar'],
    ['applications-datatables', 'Datatable Search'],
    ['applications-kanban', 'Argon development board'],
    ['applications-wizard', 'Build Your Profile'],
    ['dashboards-automotive', 'Since Last Charge'],
    ['dashboards-crm', 'Hey John!'],
    ['dashboards-default', 'Sales overview'],
    ['dashboards-landing', 'Landing source contains an empty content grid'],
    ['dashboards-smart-home', 'Cameras'],
    ['ecommerce-orders-details', 'Track order'],
    ['ecommerce-orders-list', 'Order List'],
    ['ecommerce-overview', 'Top Selling Products'],
    ['ecommerce-products-edit-product', 'Make the changes below'],
    ['ecommerce-products-new-product', 'Product Information'],
    ['ecommerce-products-product-page', 'Apple Bundle'],
    ['ecommerce-products-products-list', 'All Products'],
    ['ecommerce-referral', 'Top Referred Users'],
])->with(['light', 'dark']);

test('reference tables search sort paginate and show an empty result', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    $this->actingAs($admin);

    $page = visit(route('admin.argonGallery.examples.show', 'applications-datatables', absolute: false))
        ->assertSee('Tiger Nixon');
    $page->page()->getByRole('button', ['name' => 'Next Page', 'exact' => true])->press('Enter', ['timeout' => 5000]);
    $page->assertSee('Brielle Williamson')->assertDontSee('Tiger Nixon');
    $page->fill('input[type="search"]', 'Tokyo')->assertSee('Garrett Winters')->assertSee('Airi Satou')->assertSee('2 records');
    $page->page()->getByRole('button', ['name' => 'Name', 'exact' => true])->press('Enter', ['timeout' => 5000]);
    $page->assertScript('document.querySelector("tbody tr td")?.textContent?.trim()', 'Airi Satou');
    $page->fill('input[type="search"]', '')->click('Salary ↕');
    $page->assertScript('document.querySelector("tbody tr td")?.textContent?.trim()', 'Ashton Cox');
    $page->fill('input[type="search"]', 'missing employee')->assertSee('No matching records.')->assertNoJavaScriptErrors();
});

test('calendar events and kanban cards update only the local demo', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    $this->actingAs($admin);

    $page = visit(route('admin.argonGallery.examples.show', 'applications-calendar', absolute: false))
        ->fill('input[name="demo-event-title"]', 'Review reference pages')
        ->click('Add demo event')
        ->assertSee('Review reference pages');
    $page->click('Cyber Week')->assertSee('Agenda · 2021-03-27')->assertNoJavaScriptErrors();

    $board = visit(route('admin.argonGallery.examples.show', 'applications-kanban', absolute: false))
        ->assertSee('Design new dashboard');
    $board->page()->locator('[data-kanban-card="design"]')->press('ArrowRight', ['timeout' => 5000]);
    $board->assertScript('document.querySelector("[data-kanban-card=design]")?.closest("section")?.querySelector("h3")?.textContent?.trim().replace(/\\s+/g, " ")', 'In progress 2');
    $board->fill('input[name="demo-task-title"]', 'Check contrast')->click('Add demo task')->assertSee('Check contrast')->assertNoJavaScriptErrors();
});

test('new products validate advance and save while gallery navigation resets edit state', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    $this->actingAs($admin);
    $before = $admin->fresh()->getAttributes();

    $page = visit(route('admin.argonGallery.examples.show', 'ecommerce-products-new-product', absolute: false))
        ->assertSee('Product Information');
    $page->click('Next')->assertSee('Product Information')->assertMissing('input[name="demo-product-images"]')
        ->fill('input[name="demo-product-name"]', 'Gallery chair');
    $page->click('Next')->assertSee('0 images selected')
        ->assertScript('(() => { const input = document.querySelector("input[name=demo-product-images]"); const files = new DataTransfer(); files.items.add(new File(["demo image"], "demo.png", { type: "image/png" })); input.files = files.files; input.dispatchEvent(new Event("change", { bubbles: true })); return input.files.length; })()', 1)->assertSee('1 images selected');
    $page->click('Next')->assertSee('Shopify handle');
    $page->click('Next')->assertSee('SKU');
    $page->click('Create demo product')->assertSee('Demo product saved')->assertSee('Gallery chair · USD 99')
        ->assertNoJavaScriptErrors();
    $page->click('Back to manifest');
    visit(route('admin.argonGallery.examples.show', 'ecommerce-products-new-product', absolute: false))
        ->assertSee('Product Information')->assertScript('document.querySelector("input[name=demo-product-name]")?.value', '')->assertNoJavaScriptErrors();

    expect($admin->fresh()->getAttributes())->toBe($before);
});

test('product previews support keyboard gallery navigation and a local cart', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    $this->actingAs($admin);

    $page = visit(route('admin.argonGallery.examples.show', 'ecommerce-products-product-page', absolute: false))->inDarkMode();
    $page->page()->getByRole('button', ['name' => 'Enlarge product preview', 'exact' => true])->press('Enter', ['timeout' => 5000]);
    $page->assertSee('Product preview')->assertScript('async () => (await window.axe.run(document.querySelector("[role=dialog]"))).violations.filter(issue => ["critical", "serious"].includes(issue.impact))', []);
    $page->page()->getByRole('button', ['name' => 'Next image', 'exact' => true])->press('Enter', ['timeout' => 5000]);
    $page->assertSee('Laptop');
    $page->page()->getByRole('button', ['name' => 'Next image', 'exact' => true])->press('Escape', ['timeout' => 5000]);
    $page->fill('input[name="demo-product-quantity"]', '2')->click('Add to demo cart')->assertSee('2 × Black Apple Bundle added')->assertNoJavaScriptErrors();
});

test('smart home camera thermostat and devices respond to keyboard controls', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    $this->actingAs($admin);

    $page = visit(route('admin.argonGallery.examples.show', 'dashboards-smart-home', absolute: false))
        ->click('Office')->assertSee('Office camera')
        ->click('Pause camera')->assertSee('Paused');
    $page->page()->locator('#demo-temperature')->press('ArrowRight', ['timeout' => 5000]);
    $page->assertSee('22 °C');
    $page->page()->getByRole('switch', ['name' => 'Lights', 'exact' => true])->press('Space', ['timeout' => 5000]);
    $page->assertScript('document.querySelector("[role=switch][aria-label=Lights]")?.getAttribute("aria-checked") === "true" || [...document.querySelectorAll("[role=switch]")].filter(el => el.getAttribute("aria-checked") === "true").length === 3', true)
        ->assertNoJavaScriptErrors();
});

test('automotive map locations and playback controls stay usable without external media', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    $this->actingAs($admin);

    $page = visit(route('admin.argonGallery.examples.show', 'dashboards-automotive', absolute: false))
        ->assertSee('Since Last Charge')
        ->assertScript('document.querySelector("section[aria-label$=\"reference implementation\"] svg") !== null', true)
        ->click('Map locations');
    $page->page()->getByRole('button', ['name' => 'Miclan charging station · 891 Limarenda road', 'exact' => true])->last()->press('Enter', ['timeout' => 5000]);
    $page->assertSee('Miclan charging station · 891 Limarenda road')
        ->click('Play demo playback')->assertSee('Demo playback active');
    $page->page()->locator('#demo-volume')->press('ArrowRight', ['timeout' => 5000]);
    $page->assertSee('16/100')->assertNoJavaScriptErrors();
});

test('profile wizard preserves earlier values and finishes locally', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    $this->actingAs($admin);

    $page = visit(route('admin.argonGallery.examples.show', 'applications-wizard', absolute: false))
        ->fill('input[type="email"]', 'invalid')
        ->click('Next')->assertDontSee('What are you doing?')
        ->fill('input[type="email"]', 'demo@example.test')
        ->click('Next')->assertSee('What are you doing?')
        ->click('Next')->assertSee('Street address')
        ->click('Create demo profile')->assertSee('Demo profile created')
        ->resize(390, 844)->assertNoAccessibilityIssues()->assertNoJavaScriptErrors();
});

test('product editing uses the rich text adapter and saves a local confirmation', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    $this->actingAs($admin);

    visit(route('admin.argonGallery.examples.show', 'ecommerce-products-edit-product', absolute: false))
        ->fill('input[name="demo-product-name"]', 'Updated demo stool')
        ->fill('[contenteditable="true"]', 'Edited product description')
        ->click('Save demo changes')->assertSee('Updated demo stool · USD 99')
        ->resize(390, 844)->assertNoAccessibilityIssues()->assertNoJavaScriptErrors();
});
