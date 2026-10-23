<?php

declare(strict_types=1);

use App\Models\User;
use SoutheastCode\DevelopmentWorkflow\Actions\UpdateUserTestingPage;
use SoutheastCode\DevelopmentWorkflow\Data\UserTestingPageData;
use SoutheastCode\DevelopmentWorkflow\Models\UserTestingPage;

test('first review records the actor and timestamp once', function (): void {
    $actor = User::factory()->create();
    $page = UserTestingPage::factory()->create();

    $updated = app(UpdateUserTestingPage::class)->handle(
        $page,
        new UserTestingPageData(true, null, null, '  Review complete.  '),
        $actor,
    );

    expect($updated->tested_by)->toBe($actor->getKey())
        ->and($updated->tested_at)->toEqual(now()->startOfSecond())
        ->and($updated->approved_at)->toBeNull()
        ->and($updated->roadmap_task_at)->toBeNull()
        ->and($updated->notes)->toBe('Review complete.');
});

test('repeated notes-only saves preserve review state and actor', function (): void {
    $firstActor = User::factory()->create();
    $secondActor = User::factory()->create();
    $page = UserTestingPage::factory()->create();
    $action = app(UpdateUserTestingPage::class);

    $first = $action->handle($page, new UserTestingPageData(true, null, null, 'First notes'), $firstActor);
    $this->travelTo(now()->addDay());
    $second = $action->handle($page, new UserTestingPageData(null, null, null, 'Second notes'), $secondActor);

    expect($second->tested_by)->toBe($firstActor->getKey())
        ->and($second->tested_at)->toEqual($first->tested_at)
        ->and($second->notes)->toBe('Second notes');
});

test('roadmap and approval transitions keep independent timestamps', function (): void {
    $actor = User::factory()->create();
    $page = UserTestingPage::factory()->create();
    $action = app(UpdateUserTestingPage::class);

    $roadmap = $action->handle($page, new UserTestingPageData(null, null, true, 'Roadmap note'), $actor);
    $this->travelTo(now()->addDay());
    $approved = $action->handle($page, new UserTestingPageData(null, true, null, 'Approved note'), $actor);

    expect($roadmap->tested_at)->toBeNull()
        ->and($roadmap->roadmap_task_at)->toEqual(now()->startOfSecond()->subDay())
        ->and($approved->tested_by)->toBe($actor->getKey())
        ->and($approved->tested_at)->toEqual(now()->startOfSecond())
        ->and($approved->approved_at)->toEqual(now()->startOfSecond())
        ->and($approved->roadmap_task_at)->toEqual($roadmap->roadmap_task_at);
});

test('explicit false values clear only their owned state', function (): void {
    $actor = User::factory()->create();
    $page = UserTestingPage::factory()->create([
        'tested_by' => $actor->getKey(),
        'tested_at' => now()->subDays(3),
        'approved_at' => now()->subDays(2),
        'roadmap_task_at' => now()->subDay(),
    ]);

    $updated = app(UpdateUserTestingPage::class)->handle(
        $page,
        new UserTestingPageData(false, false, false, null),
        $actor,
    );

    expect($updated->tested_by)->toBeNull()
        ->and($updated->tested_at)->toBeNull()
        ->and($updated->approved_at)->toBeNull()
        ->and($updated->roadmap_task_at)->toBeNull();
});

test('the action reloads a stale page before applying transitions', function (): void {
    $actor = User::factory()->create();
    $page = UserTestingPage::factory()->create();
    $stalePage = $page->fresh();
    $action = app(UpdateUserTestingPage::class);

    $first = $action->handle($page, new UserTestingPageData(true, null, null, 'First'), $actor);
    $this->travelTo(now()->addHour());
    $second = $action->handle($stalePage, new UserTestingPageData(null, null, true, 'Second'), $actor);

    expect($second->tested_at)->toEqual($first->tested_at)
        ->and($second->tested_by)->toBe($actor->getKey())
        ->and($second->roadmap_task_at)->toEqual(now()->startOfSecond())
        ->and($second->notes)->toBe('Second');
});
