<?php

declare(strict_types=1);

use App\Models\User;
use SoutheastCode\Crm\Leads\Models\Lead;
use SoutheastCode\Crm\Organizations\Models\Organization;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Permission;

test('lead history navigation and readable changes work across themes and screen sizes', function (): void {
    $user = User::factory()->create();
    $user->givePermissionTo([
        Permission::findOrCreate('crm.view'),
        Permission::findOrCreate('crm.manage'),
        Permission::findOrCreate('audit.view'),
    ]);
    $organization = Organization::factory()->create(['owner_id' => $user->id]);
    $lead = Lead::factory()->create(['owner_id' => $user->id, 'organization_id' => $organization->id]);
    Activity::query()->delete();
    foreach (range(1, 16) as $number) {
        activity()->performedOn($lead)->causedBy($user)->event('updated')->log("Change {$number}");
    }
    Activity::query()->latest('id')->firstOrFail()->update([
        'attribute_changes' => ['old' => ['status' => 'new'], 'attributes' => ['status' => 'qualified']],
    ]);
    $this->actingAs($user);

    foreach ([false, true] as $dark) {
        $page = visit(route('crm.leads.show', $lead, false));
        if ($dark) {
            $page->inDarkMode();
        }
        $page->assertSee('History')
            ->assertScript('(() => { const history = document.querySelector(\'a[href$="/history"]\'); return history.previousElementSibling.textContent.includes("Convert to client") && history.nextElementSibling.textContent.includes("Archive"); })()', true)
            ->click('History')->assertPathIs(route('crm.leads.history', $lead, false))
            ->assertSee('Lead history')->assertSee('Before: new')->assertSee('After: qualified')->assertSee($user->name);
        foreach ([390, 1440] as $width) {
            $page->resize($width, 900)->assertScript('document.documentElement.scrollWidth <= window.innerWidth', true);
        }
        $page->click('button[aria-label="Page 2"]')->assertQueryStringHas('page', '2')->assertDontSee('After: qualified')
            ->click('Back to lead')->assertPathIs(route('crm.leads.show', $lead, false))->assertNoJavaScriptErrors();
    }
});
