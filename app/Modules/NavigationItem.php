<?php

declare(strict_types=1);

namespace App\Modules;

final readonly class NavigationItem
{
    public function __construct(
        public string $key,
        public string $label,
        public string $route,
        public ?string $parent = null,
        public ?string $icon = null,
        public ?string $searchDescription = null,
        public bool $exact = false,
        public int $order = 0,
    ) {}
}
