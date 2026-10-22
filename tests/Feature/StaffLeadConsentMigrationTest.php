<?php

declare(strict_types=1);

use SoutheastCode\Crm\Leads\Models\Lead;

test('fresh CRM schema stores undocumented and affirmative consent without correction prerequisites', function (): void {
    $this->travelTo(now()->startOfSecond());
    $staff = Lead::factory()->create(['consented_at' => null]);
    $public = Lead::factory()->create(['consented_at' => now(), 'intake_snapshot' => ['name' => 'Visitor']]);

    $this->artisan('migrate', ['--force' => true])->assertSuccessful();
    $this->artisan('migrate', ['--force' => true])->assertSuccessful();

    expect($staff->refresh()->consented_at)->toBeNull()
        ->and($public->refresh()->consented_at?->equalTo(now()))->toBeTrue()
        ->and($public->intake_snapshot)->toBe(['name' => 'Visitor']);
});
