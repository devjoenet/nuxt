import { dashboard } from "@/routes";
import { dashboard as adminDashboard } from "@/routes/admin";
import { index as developmentIndex } from "@/routes/development";
import { index as leadsIndex } from "@/routes/crm/leads";
import { edit as profileEdit } from "@/routes/profile";
import { usePage } from "@inertiajs/vue3";
import { computed, type ComputedRef, type MaybeRefOrGetter, toValue } from "vue";

interface BreadcrumbSection {
  label: string;
  href: string;
}

interface WorkspaceBreadcrumbItem {
  label: string;
  href?: string;
  current?: boolean;
}

const sections: Record<string, BreadcrumbSection> = {
  admin: { label: "Admin", href: adminDashboard.url() },
  crm: { label: "CRM", href: leadsIndex.url() },
  development: { label: "Development", href: developmentIndex.url() },
  settings: { label: "Settings", href: profileEdit.url() },
};

export function useWorkspaceBreadcrumbs(title: MaybeRefOrGetter<string>): ComputedRef<WorkspaceBreadcrumbItem[]> {
  const page = usePage();

  return computed(() => {
    const currentTitle = toValue(title);
    const section = sections[page.url.split("?")[0].split("/").filter(Boolean)[0] ?? ""];
    const items: WorkspaceBreadcrumbItem[] = [{ label: "Workspace", href: dashboard.url() }];

    if (section && section.label !== currentTitle) {
      items.push({ label: section.label, href: section.href });
    }

    if (/^\/crm\/leads\/[^/?]+/.test(page.url)) {
      items.push({ label: "Leads", href: leadsIndex.url() });
    }

    items.push({ label: currentTitle, current: true });

    return items;
  });
}
