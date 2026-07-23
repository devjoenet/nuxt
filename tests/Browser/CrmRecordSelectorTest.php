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
    $page->page()->locator('[data-slot="input"] input[aria-expanded="true"]')->fill('Task Twenty Four Contact');
    $page->page()->getByRole('option', ['name' => "{$contact->name} — {$contact->email}", 'exact' => true])->press('Enter');

    $page->page()->locator('#lead-organization')->click();
    $page->page()->locator('[data-slot="input"] input[aria-expanded="true"]')->fill('Task Twenty Four Organization');
    $page->page()->getByRole('option', ['name' => $organization->name, 'exact' => false])->press('Enter');

    $page
        ->assertValue('input[name="contact_id"]', $contact->uuid)
        ->assertValue('input[name="organization_id"]', $organization->uuid)
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs();
});
