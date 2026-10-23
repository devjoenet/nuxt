<?php

declare(strict_types=1);

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use SoutheastCode\Crm\Contacts\Models\Contact;
use SoutheastCode\Crm\FollowUps\Models\FollowUpTask;
use SoutheastCode\Crm\Leads\Models\Lead;
use SoutheastCode\Crm\Organizations\Models\Organization;

test('lead forms save and follow ups complete through package transport', function (): void {
    app(RolePermissionSeeder::class)->run();
    $admin = User::factory()->create()->assignRole('Admin');
    $contact = Contact::factory()->create(['owner_id' => $admin->id, 'name' => 'Workflow Contact']);
    $organization = Organization::factory()->create(['owner_id' => $admin->id, 'name' => 'Workflow Organization']);
    $this->actingAs($admin);
    $page = visit('/crm/leads/create')->resize(390, 844)->assertSee('Contact and opportunity');
    $page->page()->locator('#lead-contact')->click();
    $page->page()->getByRole('option', ['name' => $contact->name.' — '.$contact->email, 'exact' => true])->click();
    $page->page()->locator('#lead-organization')->click();
    $page->assertSee($organization->name);
    $page->page()->getByRole('option', ['name' => $organization->name, 'exact' => false])->click();
    $page->fill('project_details', 'A portable lead workflow')->click('Create lead')
        ->assertSee('A portable lead workflow')->assertNoJavaScriptErrors();
    $lead = Lead::query()->sole();
    $page->assertPathIs('/crm/leads/'.$lead->uuid)
        ->fill('title', 'Portable follow-up')->fill('due_at', '2026-12-01T10:00')
        ->click('Add follow-up')->assertSee('Portable follow-up')
        ->click('[aria-label="Complete follow-up"]')->assertMissing('[aria-label="Complete follow-up"]')
        ->assertNoJavaScriptErrors();
    expect(FollowUpTask::query()->sole()->status->value)->toBe('completed');
    $page->script('document.documentElement.classList.add("dark")');
    $page->assertScript('document.documentElement.scrollWidth <= window.innerWidth', true)
        ->screenshot(filename: 'task12-lead-workflow-dark');
});

test('lead list searches submitted company in light and dark layouts', function (): void {
    app(RolePermissionSeeder::class)->run();
    $user = User::factory()->create()->assignRole('Sales/Ops');
    $contact = Contact::factory()->create(['owner_id' => null, 'name' => 'Submitted Inquiry']);
    Lead::factory()->create(['owner_id' => null, 'contact_id' => $contact->id, 'intake_snapshot' => ['company' => 'Searchable Submitted Business']]);
    $otherContact = Contact::factory()->create(['owner_id' => $user->id, 'name' => 'Unrelated Inquiry']);
    Lead::factory()->create(['owner_id' => $user->id, 'contact_id' => $otherContact->id]);
    $this->actingAs($user);
    $page = visit('/crm/leads')->fill('lead-search', 'Searchable Submitted Business')
        ->assertSee('Submitted Inquiry')->assertDontSee('Unrelated Inquiry')->assertNoJavaScriptErrors();
    foreach ([false, true] as $dark) {
        if ($dark) {
            $page->script('document.documentElement.classList.add("dark")');
        }
        $page->resize(390, 844)->assertScript('document.documentElement.scrollWidth <= window.innerWidth', true);
        $page->resize(1440, 900)->assertSee('Submitted Inquiry')->assertDontSee('Unrelated Inquiry')->assertNoJavaScriptErrors();
    }
    $page->screenshot(filename: 'task12-company-search');
});
