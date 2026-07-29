<?php

declare(strict_types=1);

namespace App\Enums;

enum DevelopmentTaskStatus: string
{
    case Submitted = 'submitted';
    case NeedsInformation = 'needs_information';
    case UnderReview = 'under_review';
    case Planned = 'planned';
    case InProgress = 'in_progress';
    case Blocked = 'blocked';
    case ReadyForTesting = 'ready_for_testing';
    case ChangesRequested = 'changes_requested';
    case Completed = 'completed';
    case Rejected = 'rejected';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return str($this->value)->replace('_', ' ')->title()->toString();
    }

    public function canTransitionTo(self $status): bool
    {
        return in_array($status, $this->allowedTransitions(), true);
    }

    /** @return array<int, self> */
    private function allowedTransitions(): array
    {
        return match ($this) {
            self::Submitted => [
                self::NeedsInformation,
                self::UnderReview,
                self::Cancelled,
            ],
            self::NeedsInformation => [
                self::UnderReview,
                self::Cancelled,
            ],
            self::UnderReview => [
                self::Planned,
                self::Rejected,
                self::Cancelled,
            ],
            self::Planned => [
                self::InProgress,
                self::Cancelled,
            ],
            self::InProgress => [
                self::Blocked,
                self::ReadyForTesting,
                self::Cancelled,
            ],
            self::Blocked => [
                self::InProgress,
                self::Cancelled,
            ],
            self::ReadyForTesting => [
                self::ChangesRequested,
                self::Completed,
                self::Cancelled,
            ],
            self::ChangesRequested => [
                self::InProgress,
                self::Cancelled,
            ],
            self::Completed, self::Rejected, self::Cancelled => [],
        };
    }

    public function isTerminal(): bool
    {
        return in_array($this, [
            self::Completed,
            self::Rejected,
            self::Cancelled,
        ], true);
    }

    public function timestampColumn(): string
    {
        return match ($this) {
            self::Submitted => 'submitted_at',
            self::NeedsInformation => 'information_requested_at',
            self::UnderReview => 'review_started_at',
            self::Planned => 'planned_at',
            self::InProgress => 'started_at',
            self::Blocked => 'blocked_at',
            self::ReadyForTesting => 'ready_for_testing_at',
            self::ChangesRequested => 'changes_requested_at',
            self::Completed => 'completed_at',
            self::Rejected => 'rejected_at',
            self::Cancelled => 'cancelled_at',
        };
    }
}
