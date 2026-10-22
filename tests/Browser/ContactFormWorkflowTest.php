<?php

declare(strict_types=1);

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use SoutheastCode\Crm\Contacts\Models\Contact;
use SoutheastCode\Crm\Organizations\Models\Organization;

test('contact form selects an organization by keyboard and persists the relationship', function (): void {
    app(RolePermissionSeeder::class)->run();
    $user = User::factory()->create()->assignRole('Admin');
    $organization = Organization::factory()->create(['name' => 'Keyboard Account', 'owner_id' => $user->id]);
    $this->actingAs($user);
    $page = visit(route('crm.contacts.create', absolute: false))
        ->resize(390, 844)
        ->assertSee('Contact details')
        ->fill('name', 'Keyboard Contact')
        ->fill('email', 'keyboard@example.test');
    $page->page()->locator('#contact-organization')->click();
    $page->page()->locator('[data-slot="input"] input[aria-expanded="true"]')->fill('Keyboard');
    $page->assertSee('Keyboard Account');
    $page->page()->locator('[data-slot="input"] input[aria-expanded="true"]')->press('ArrowDown');
    $page->page()->locator('[data-slot="input"] input[aria-expanded="true"]')->press('Enter');
    $page->assertValue('input[name="organization_id"]', $organization->uuid)
        ->click('Create contact')
        ->assertSee('Keyboard Contact')
        ->assertSee('Keyboard Account')
        ->assertNoJavaScriptErrors();
    $contact = Contact::where('email', 'keyboard@example.test')->sole();
    expect($contact->organization_id)->toBe($organization->id);
    $page->assertPathIs(route('crm.contacts.show', $contact, false));
});
