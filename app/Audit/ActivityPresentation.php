<?php

declare(strict_types=1);

namespace App\Audit;

final readonly class ActivityPresentation
{
    public function __construct(public string $label, public ?string $url = null) {}
}
