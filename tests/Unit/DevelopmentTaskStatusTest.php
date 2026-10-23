<?php

declare(strict_types=1);

use SoutheastCode\DevelopmentWorkflow\Enums\DevelopmentRequestType;
use SoutheastCode\DevelopmentWorkflow\Enums\DevelopmentTaskArea;
use SoutheastCode\DevelopmentWorkflow\Enums\DevelopmentTaskLinkType;
use SoutheastCode\DevelopmentWorkflow\Enums\DevelopmentTaskStatus;
use SoutheastCode\DevelopmentWorkflow\Enums\DevelopmentTestRunStatus;

test('the lifecycle defines every allowed and forbidden status transition', function (): void {
    $allowedTransitions = [
        DevelopmentTaskStatus::Submitted->value => [
            DevelopmentTaskStatus::NeedsInformation,
            DevelopmentTaskStatus::UnderReview,
            DevelopmentTaskStatus::Cancelled,
        ],
        DevelopmentTaskStatus::NeedsInformation->value => [
            DevelopmentTaskStatus::UnderReview,
            DevelopmentTaskStatus::Cancelled,
        ],
        DevelopmentTaskStatus::UnderReview->value => [
            DevelopmentTaskStatus::Planned,
            DevelopmentTaskStatus::Rejected,
            DevelopmentTaskStatus::Cancelled,
        ],
        DevelopmentTaskStatus::Planned->value => [
            DevelopmentTaskStatus::InProgress,
            DevelopmentTaskStatus::Cancelled,
        ],
        DevelopmentTaskStatus::InProgress->value => [
            DevelopmentTaskStatus::Blocked,
            DevelopmentTaskStatus::ReadyForTesting,
            DevelopmentTaskStatus::Cancelled,
        ],
        DevelopmentTaskStatus::Blocked->value => [
            DevelopmentTaskStatus::InProgress,
            DevelopmentTaskStatus::Cancelled,
        ],
        DevelopmentTaskStatus::ReadyForTesting->value => [
            DevelopmentTaskStatus::ChangesRequested,
            DevelopmentTaskStatus::Completed,
            DevelopmentTaskStatus::Cancelled,
        ],
        DevelopmentTaskStatus::ChangesRequested->value => [
            DevelopmentTaskStatus::InProgress,
            DevelopmentTaskStatus::Cancelled,
        ],
        DevelopmentTaskStatus::Completed->value => [],
        DevelopmentTaskStatus::Rejected->value => [],
        DevelopmentTaskStatus::Cancelled->value => [],
    ];

    foreach (DevelopmentTaskStatus::cases() as $source) {
        foreach (DevelopmentTaskStatus::cases() as $target) {
            $expected = in_array($target, $allowedTransitions[$source->value], true);

            expect($source->canTransitionTo($target))->toBe(
                $expected,
                "Unexpected {$source->value} to {$target->value} transition rule.",
            );
        }
    }
});

test('terminal statuses and status timestamps are explicit', function (): void {
    $timestampColumns = [];

    foreach (DevelopmentTaskStatus::cases() as $status) {
        $timestampColumns[] = $status->timestampColumn();
    }

    expect(DevelopmentTaskStatus::Completed->isTerminal())->toBeTrue()
        ->and(DevelopmentTaskStatus::Rejected->isTerminal())->toBeTrue()
        ->and(DevelopmentTaskStatus::Cancelled->isTerminal())->toBeTrue()
        ->and(DevelopmentTaskStatus::ReadyForTesting->isTerminal())->toBeFalse()
        ->and($timestampColumns)->toHaveCount(count(DevelopmentTaskStatus::cases()))
        ->and(array_unique($timestampColumns))->toHaveCount(count(DevelopmentTaskStatus::cases()));
});

test('development metadata enums expose stable labels and test outcomes', function (): void {
    expect(DevelopmentRequestType::TechnicalDebt->label())->toBe('Technical Debt')
        ->and(DevelopmentTaskArea::MarketingSite->label())->toBe('Marketing Site')
        ->and(DevelopmentTaskLinkType::PullRequest->label())->toBe('Pull Request')
        ->and(DevelopmentTestRunStatus::Pending->isComplete())->toBeFalse()
        ->and(DevelopmentTestRunStatus::Accepted->isComplete())->toBeTrue()
        ->and(DevelopmentTestRunStatus::ChangesRequested->isComplete())->toBeTrue();
});
