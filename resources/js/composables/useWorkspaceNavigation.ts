import { usePage } from "@inertiajs/vue3";
import type { AppPageProps } from "@/types";
import type { CommandPaletteGroup, CommandPaletteItem, NavigationMenuItem } from "@nuxt/ui";
import { computed, type ComputedRef } from "vue";

export type SearchableNavigationItem = Omit<NavigationMenuItem, "children"> & {
  children?: SearchableNavigationItem[];
  searchDescription?: string;
};

interface WorkspaceNavigationOptions {
  onNavigate?: () => void;
}

interface WorkspaceNavigation {
  navigationGroups: ComputedRef<SearchableNavigationItem[][]>;
  searchGroups: ComputedRef<CommandPaletteGroup[]>;
}

function navigationSearchItems(items: SearchableNavigationItem[], parentLabels: string[] = [], parentIcon?: string): CommandPaletteItem[] {
  return items.flatMap((item): CommandPaletteItem[] => {
    const label = typeof item.label === "string" ? item.label : undefined;
    const labels = label ? [...parentLabels, label] : parentLabels;
    const icon = typeof item.icon === "string" ? item.icon : parentIcon;
    const children = Array.isArray(item.children) ? navigationSearchItems(item.children, labels, icon) : [];

    if (children.length > 0) {
      return children;
    }

    if (!label || typeof item.to !== "string") {
      return [];
    }

    const { chip: _chip, ui: _ui, children: _children, defaultOpen: _defaultOpen, searchDescription, type: _type, ...searchItem } = item;

    return [
      {
        ...searchItem,
        label,
        icon,
        suffix: parentLabels.at(-1),
        description: searchDescription,
      },
    ];
  });
}

export function useWorkspaceNavigation(options: WorkspaceNavigationOptions = {}): WorkspaceNavigation {
  const page = usePage<AppPageProps>();
  const decorate = (item: SearchableNavigationItem): SearchableNavigationItem => ({
    ...item,
    onSelect: () => options.onNavigate?.(),
    children: item.children?.map(decorate),
  });
  const navigationGroups = computed<SearchableNavigationItem[][]>(() => [(page.props.workspace?.navigation ?? []).map(decorate)]);

  const searchGroups = computed<CommandPaletteGroup[]>(() => [
    {
      id: "links",
      label: "Go to",
      items: navigationGroups.value.flatMap((group) => navigationSearchItems(group)),
    },
  ]);

  return { navigationGroups, searchGroups };
}
