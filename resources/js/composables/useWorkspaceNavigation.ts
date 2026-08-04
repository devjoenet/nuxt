import { dashboard as adminDashboard } from "@/routes/admin";
import { index as adminAuditIndex } from "@/routes/admin/audit";
import { index as adminJobsIndex } from "@/routes/admin/jobs";
import { index as adminRolesIndex } from "@/routes/admin/roles";
import { edit as adminSettingsEdit } from "@/routes/admin/settings";
import { index as adminUsersIndex } from "@/routes/admin/users";
import { dashboard } from "@/routes";
import { index as contactsIndex } from "@/routes/crm/contacts";
import { index as leadsIndex } from "@/routes/crm/leads";
import { index as organizationsIndex } from "@/routes/crm/organizations";
import { index as developmentIndex } from "@/routes/development";
import { create as developmentRequestCreate, index as developmentRequestsIndex } from "@/routes/development/requests";
import { index as developmentReviewIndex } from "@/routes/development/review";
import { index as roadmapIndex } from "@/routes/development/roadmap";
import { index as userTestingIndex } from "@/routes/development/userTesting";
import { edit as profileEdit } from "@/routes/profile";
import { show as securityShow } from "@/routes/security";
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
  const auth = useAuth();
  const closeNavigation = (): void => options.onNavigate?.();
  const can = (permission: string): boolean => auth.value.user?.can?.[permission] === true;
  const canAccessDevelopment = (): boolean => can("development.requests.create") || can("development.requests.review") || can("development.roadmap.manage") || can("development.user_testing.manage") || auth.value.user?.has_pending_development_tests === true;

  const navigationGroups = computed<SearchableNavigationItem[][]>(() => [
    [
      {
        label: "Home",
        icon: "i-ph-house",
        to: dashboard.url(),
        searchDescription: "Workspace · Recent leads · Follow-ups",
        onSelect: closeNavigation,
      },
      ...(can("crm.view")
        ? [
            {
              label: "CRM",
              icon: "i-ph-users",
              to: leadsIndex.url(),
              type: "trigger" as const,
              defaultOpen: true,
              children: [
                { label: "Leads", to: leadsIndex.url(), searchDescription: "CRM leads", onSelect: closeNavigation },
                { label: "Organizations", to: organizationsIndex.url(), searchDescription: "CRM organizations", onSelect: closeNavigation },
                { label: "Contacts", to: contactsIndex.url(), searchDescription: "CRM contacts", onSelect: closeNavigation },
              ],
              onSelect: closeNavigation,
            },
          ]
        : []),
      ...(canAccessDevelopment()
        ? [
            {
              label: "Development",
              icon: "i-ph-code",
              to: developmentIndex.url(),
              type: "trigger" as const,
              defaultOpen: true,
              children: [
                { label: "Overview", to: developmentIndex.url(), exact: true, searchDescription: "Development workspace · Roadmap work", onSelect: closeNavigation },
                ...(can("development.requests.create")
                  ? [
                      { label: "My requests", to: developmentRequestsIndex.url(), exact: true, searchDescription: "Development requests · Active requests", onSelect: closeNavigation },
                      { label: "Submit request", to: developmentRequestCreate.url(), searchDescription: "Submit a development request · Describe the problem", onSelect: closeNavigation },
                    ]
                  : []),
                ...(can("development.requests.review") ? [{ label: "Review queue", to: developmentReviewIndex.url(), searchDescription: "Development review queue", onSelect: closeNavigation }] : []),
                ...(can("development.roadmap.manage") ? [{ label: "Roadmap", to: roadmapIndex.url(), searchDescription: "Development roadmap · Planned active and blocked work", onSelect: closeNavigation }] : []),
                ...(can("development.user_testing.manage") || auth.value.user?.has_pending_development_tests ? [{ label: "User testing", to: userTestingIndex.url(), searchDescription: "Task-specific testing · Testing queue", onSelect: closeNavigation }] : []),
              ],
              onSelect: closeNavigation,
            },
          ]
        : []),
      ...(can("admin.dashboard.view")
        ? [
            {
              label: "Admin",
              icon: "i-ph-shield-check",
              to: adminDashboard.url(),
              exact: true,
              type: "trigger" as const,
              defaultOpen: true,
              children: [
                { label: "Overview", to: adminDashboard.url(), exact: true, searchDescription: "Admin dashboard · Recent activity · Recent users", onSelect: closeNavigation },
                ...(can("admin.users.manage") ? [{ label: "Users", to: adminUsersIndex.url(), searchDescription: "Admin users · Invite a user", onSelect: closeNavigation }] : []),
                ...(can("admin.roles.manage") ? [{ label: "Roles", to: adminRolesIndex.url(), searchDescription: "Roles and permissions · Choose a role", onSelect: closeNavigation }] : []),
                ...(can("audit.view") ? [{ label: "Audit log", to: adminAuditIndex.url(), searchDescription: "Activity history · Sensitive workspace changes", onSelect: closeNavigation }] : []),
                ...(can("settings.manage") ? [{ label: "Business settings", to: adminSettingsEdit.url(), searchDescription: "Workspace defaults", onSelect: closeNavigation }] : []),
                ...(can("jobs.view") ? [{ label: "Jobs & queues", to: adminJobsIndex.url(), searchDescription: "Jobs and queues · Queue health · Horizon monitoring", onSelect: closeNavigation }] : []),
              ],
              onSelect: closeNavigation,
            },
          ]
        : []),
    ],
    [
      {
        label: "Settings",
        to: profileEdit.url(),
        icon: "i-ph-gear-six",
        defaultOpen: true,
        type: "trigger" as const,
        children: [
          { label: "Profile", to: profileEdit.url(), exact: true, searchDescription: "Profile settings", onSelect: closeNavigation },
          { label: "Security", to: securityShow.url(), searchDescription: "Security settings · Password · Two-factor authentication · Account", onSelect: closeNavigation },
        ],
        onSelect: closeNavigation,
      },
    ],
  ]);

  const searchGroups = computed<CommandPaletteGroup[]>(() => [
    {
      id: "links",
      label: "Go to",
      items: navigationGroups.value.flatMap((group) => navigationSearchItems(group)),
    },
  ]);

  return { navigationGroups, searchGroups };
}
