import type { AppPageProps } from "@/types";
import { dashboard } from "@/routes";
import { usePage } from "@inertiajs/vue3";
import { computed, type ComputedRef, type MaybeRefOrGetter, toValue } from "vue";

interface WorkspaceBreadcrumbItem {
  label: string;
  href?: string;
  current?: boolean;
}

export function useWorkspaceBreadcrumbs(title: MaybeRefOrGetter<string>): ComputedRef<WorkspaceBreadcrumbItem[]> {
  const page = usePage<AppPageProps>();

  return computed(() => {
    const currentTitle = toValue(title);
    const items: WorkspaceBreadcrumbItem[] = [{ label: "Workspace", href: dashboard.url() }];
    for (const item of page.props.workspace?.breadcrumbs ?? []) {
      if (item.label !== currentTitle && item.label !== "Home") items.push(item);
    }

    items.push({ label: currentTitle, current: true });

    return items;
  });
}
