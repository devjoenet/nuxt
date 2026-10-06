<?php

declare(strict_types=1);

use App\Models\Lead;
use App\Models\User;

test('consent correction only clears provably assumed staff consent and is repeatable', function (): void {
    $this->travelTo(Illuminate\Support\Carbon::parse('2026-10-06 10:00:00'));
    $actor = User::factory()->create();
    $staff = Lead::factory()->create();
    $staff->activities()->create(['type' => 'lead_created', 'user_id' => $actor->id, 'description' => 'Staff entry']);
    $staff->delete();

    $unknown = Lead::factory()->create();
    $public = Lead::factory()->create();
    $public->activities()->create(['type' => 'public_intake_created', 'description' => 'Public intake']);
    $conflicting = Lead::factory()->create();
    $conflicting->activities()->create(['type' => 'lead_created', 'user_id' => $actor->id, 'description' => 'Staff entry']);
    $conflicting->activities()->create(['type' => 'public_intake_created', 'description' => 'Public intake']);
    $snapshot = Lead::factory()->create(['intake_snapshot' => ['name' => 'Visitor']]);
    $snapshot->activities()->create(['type' => 'lead_created', 'user_id' => $actor->id, 'description' => 'Staff entry']);
    $laterConsent = Lead::factory()->create(['consented_at' => now()->addDay()]);
    $laterConsent->activities()->create(['type' => 'lead_created', 'user_id' => $actor->id, 'description' => 'Staff entry']);

    $migration = require database_path('migrations/2026_10_06_123304_clear_assumed_staff_lead_consent.php');

    $migration->up();
    $migration->up();
    $migration->down();

    expect($staff->refresh()->consented_at)->toBeNull();
    foreach ([$unknown, $public, $conflicting, $snapshot] as $lead) {
        expect($lead->refresh()->consented_at?->equalTo(now()))->toBeTrue();
    }
    expect($laterConsent->refresh()->consented_at?->equalTo(now()->addDay()))->toBeTrue();
});
