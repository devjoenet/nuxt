<?php

declare(strict_types=1);

use App\Models\DevelopmentTask;
use App\Models\User;
use SoutheastCode\Crm\Contacts\Models\Contact;
use SoutheastCode\Crm\Organizations\Models\Organization;
use Spatie\Permission\Models\Permission;

test('record history links and back navigation work across themes and screen sizes', function (): void {
    $user = User::factory()->create();
    $user->givePermissionTo(Permission::findOrCreate('crm.view'), Permission::findOrCreate('audit.view'));
    $records = [
        [Contact::factory()->create(['owner_id' => $user->id]), 'crm.contacts', 'Contact'],
        [Organization::factory()->create(['owner_id' => $user->id]), 'crm.organizations', 'Organization'],
        [DevelopmentTask::factory()->create(['submitter_id' => $user->id]), 'development.tasks', 'Development task'],
    ];
    $this->actingAs($user);
    foreach ($records as [$record, $prefix, $label]) {
        foreach ([false, true] as $dark) {
            $page = visit(route("{$prefix}.show", $record, false));
            if ($dark) {
                $page->inDarkMode();
            }
            $page->click('History')->assertPathIs(route("{$prefix}.history", $record, false))->assertSee("{$label} history");
            foreach ([390, 1440] as $width) {
                $page->resize($width, 900)->assertScript('document.documentElement.scrollWidth <= window.innerWidth', true);
            }
            $page->click('Back to '.mb_strtolower($label))->assertPathIs(route("{$prefix}.show", $record, false))->assertNoJavaScriptErrors();
        }
    }
});
