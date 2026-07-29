<?php

declare(strict_types=1);

namespace App\Enums;

enum DevelopmentTestRunStatus: string
{
    case Pending = 'pending';
    case Accepted = 'accepted';
    case ChangesRequested = 'changes_requested';

    public function label(): string
    {
        return str($this->value)->replace('_', ' ')->title()->toString();
    }

    public function isComplete(): bool
    {
        return $this !== self::Pending;
    }
}
