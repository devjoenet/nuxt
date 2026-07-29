<?php

declare(strict_types=1);

namespace App\Enums;

enum DevelopmentTaskLinkType: string
{
    case Page = 'page';
    case Test = 'test';
    case Branch = 'branch';
    case Issue = 'issue';
    case PullRequest = 'pull_request';
    case Commit = 'commit';
    case Deployment = 'deployment';
    case Design = 'design';
    case Reference = 'reference';
    case External = 'external';

    public function label(): string
    {
        return str($this->value)->replace('_', ' ')->title()->toString();
    }
}
