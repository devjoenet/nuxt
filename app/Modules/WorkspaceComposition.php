<?php

declare(strict_types=1);

namespace App\Modules;

use Illuminate\Http\Request;
use LogicException;

final class WorkspaceComposition
{
    public function __construct(private ModuleRegistry $registry) {}

    /** @return array<string, mixed> */
    public function dashboard(WorkspaceUser $user): array
    {
        $props = [];
        foreach ($this->registry->all() as $module) {
            if ($module->workspaceDashboard !== null) {
                $props = $this->merge($props, app($module->workspaceDashboard)->props($user));
            }
        }

        return $props;
    }

    /** @return array<string, mixed> */
    public function userContext(WorkspaceUser $user): array
    {
        $props = [];
        foreach ($this->registry->all() as $module) {
            if ($module->userContext !== null) {
                $props = $this->merge($props, app($module->userContext)->props($user));
            }
        }

        return $props;
    }

    /** @return list<array{label: string, value: ?int, url: string, attention: bool}> */
    public function adminStats(WorkspaceUser $user): array
    {
        $stats = [];
        foreach ($this->registry->all() as $module) {
            if ($module->adminSummary !== null) {
                array_push($stats, ...app($module->adminSummary)->stats($user));
            }
        }

        return $stats;
    }

    /** @return array{navigation: list<array<string, mixed>>, breadcrumbs: list<array{label: string, href: string}>} */
    public function navigation(WorkspaceUser $user, Request $request): array
    {
        $items = [];
        foreach ($this->registry->all() as $module) {
            foreach ($module->navigation as $provider) {
                foreach (app($provider)->items($user) as $item) {
                    if (isset($items[$item->key])) {
                        throw new LogicException('Conflicting navigation key: '.$item->key);
                    }

                    $items[$item->key] = $item;
                }
            }
        }

        uasort($items, fn (NavigationItem $a, NavigationItem $b): int => $a->order <=> $b->order);
        $render = function (NavigationItem $item) use (&$render, $items): array {
            $children = array_values(array_filter($items, fn (NavigationItem $child): bool => $child->parent === $item->key));

            return array_filter([
                'key' => $item->key, 'label' => $item->label, 'to' => route($item->route, absolute: false),
                'icon' => $item->icon, 'exact' => $item->exact, 'searchDescription' => $item->searchDescription,
                'children' => $children === [] ? null : array_map($render, $children),
                'type' => $children === [] ? null : 'trigger', 'defaultOpen' => $children === [] ? null : true,
            ], fn (mixed $value): bool => $value !== null);
        };
        $roots = array_values(array_filter($items, fn (NavigationItem $item): bool => $item->parent === null));
        // Keep host ordering stable without knowing any domain keys.
        $navigation = array_map($render, $roots);
        $breadcrumbs = [];
        $path = '/'.$request->path();
        foreach ($navigation as $root) {
            $matches = array_filter($root['children'] ?? [$root], fn (array $item): bool => $path === $item['to'] || (! $item['exact'] && str_starts_with($path, mb_rtrim($item['to'], '/').'/')));
            if ($matches === [] && $path !== $root['to'] && ! str_starts_with($path, mb_rtrim($root['to'], '/').'/')) {
                continue;
            }

            $breadcrumbs[] = ['label' => $root['label'], 'href' => $root['to']];
            $match = array_values($matches)[0] ?? $root;
            if ($match['label'] !== $root['label'] && $path !== $match['to']) {
                $breadcrumbs[] = ['label' => $match['label'], 'href' => $match['to']];
            }

            break;
        }

        return ['navigation' => $navigation, 'breadcrumbs' => $breadcrumbs];
    }

    /** @param array<string, mixed> $existing
     * @param  array<string, mixed>  $incoming
     * @return array<string, mixed>
     */
    private function merge(array $existing, array $incoming): array
    {
        if (array_intersect_key($existing, $incoming) !== []) {
            throw new LogicException('Conflicting module response property.');
        }

        return [...$existing, ...$incoming];
    }
}
