<script setup lang="ts">
import type { CommandPaletteItem, NavigationMenuItem } from "@nuxt/ui";
import { index as contactsIndex } from "@/routes/crm/contacts";
import { index as leadsIndex } from "@/routes/crm/leads";
import { index as organizationsIndex } from "@/routes/crm/organizations";
import { index as jobsIndex } from "@/routes/admin/jobs";
import { index as developmentIndex } from "@/routes/development";
import { create as developmentRequestCreate, index as developmentRequestsIndex } from "@/routes/development/requests";
import { index as developmentReviewIndex } from "@/routes/development/review";
import { index as roadmapIndex } from "@/routes/development/roadmap";
import { index as userTestingIndex } from "@/routes/development/userTesting";

type SearchableNavigationItem = Omit<NavigationMenuItem, "children"> & {
  children?: SearchableNavigationItem[];
  searchDescription?: string;
};

const auth = useAuth();

const open = ref(false);

function closeNavigation(): void {
  open.value = false;
}

const links = computed<SearchableNavigationItem[][]>(() => [
  [
    {
      label: "Home",
      icon: "i-ph-house",
      to: "/dashboard",
      searchDescription: "Workspace · Recent leads · Follow-ups",
      onSelect: closeNavigation,
    },
    ...(auth.value.user?.can?.["crm.view"]
      ? [
          {
            label: "CRM",
            icon: "i-ph-users",
            to: leadsIndex.url(),
            type: "trigger" as const,
            defaultOpen: true,
            children: [
              { label: "Leads", to: leadsIndex.url(), searchDescription: "CRM leads" },
              { label: "Organizations", to: organizationsIndex.url(), searchDescription: "CRM organizations" },
              { label: "Contacts", to: contactsIndex.url(), searchDescription: "CRM contacts" },
            ],
            onSelect: closeNavigation,
          },
        ]
      : []),
    ...(auth.value.user?.can?.["development.requests.create"] || auth.value.user?.can?.["development.requests.review"] || auth.value.user?.can?.["development.roadmap.manage"] || auth.value.user?.can?.["development.user_testing.manage"] || auth.value.user?.has_pending_development_tests
      ? [
          {
            label: "Development",
            icon: "i-ph-code",
            to: developmentIndex.url(),
            type: "trigger" as const,
            defaultOpen: true,
            children: [
              { label: "Overview", to: developmentIndex.url(), exact: true, searchDescription: "Development workspace · Roadmap work" },
              ...(auth.value.user?.can?.["development.requests.create"]
                ? [
                    { label: "My requests", to: developmentRequestsIndex.url(), exact: true, searchDescription: "Development requests · Active requests" },
                    { label: "Submit request", to: developmentRequestCreate.url(), searchDescription: "Submit a development request · Describe the problem" },
                  ]
                : []),
              ...(auth.value.user?.can?.["development.requests.review"] ? [{ label: "Review queue", to: developmentReviewIndex.url(), searchDescription: "Development review queue" }] : []),
              ...(auth.value.user?.can?.["development.roadmap.manage"] ? [{ label: "Roadmap", to: roadmapIndex.url(), searchDescription: "Development roadmap · Planned active and blocked work" }] : []),
              ...(auth.value.user?.can?.["development.user_testing.manage"] || auth.value.user?.has_pending_development_tests ? [{ label: "User testing", to: userTestingIndex.url(), searchDescription: "Task-specific testing · Testing queue" }] : []),
            ],
            onSelect: closeNavigation,
          },
        ]
      : []),
    ...(auth.value.user?.can?.["admin.dashboard.view"]
      ? [
          {
            label: "Admin",
            icon: "i-ph-shield-check",
            to: "/admin",
            exact: true,
            type: "trigger" as const,
            defaultOpen: true,
            children: [
              { label: "Overview", to: "/admin", exact: true, searchDescription: "Admin dashboard · Recent activity · Recent users" },
              ...(auth.value.user?.can?.["admin.users.manage"] ? [{ label: "Users", to: "/admin/users", searchDescription: "Admin users · Invite a user" }] : []),
              ...(auth.value.user?.can?.["admin.roles.manage"] ? [{ label: "Roles", to: "/admin/roles", searchDescription: "Roles and permissions · Choose a role" }] : []),
              ...(auth.value.user?.can?.["audit.view"] ? [{ label: "Audit log", to: "/admin/audit", searchDescription: "Activity history · Sensitive workspace changes" }] : []),
              ...(auth.value.user?.can?.["settings.manage"] ? [{ label: "Business settings", to: "/admin/settings", searchDescription: "Workspace defaults" }] : []),
              ...(auth.value.user?.can?.["jobs.view"] ? [{ label: "Jobs & queues", to: jobsIndex.url(), searchDescription: "Jobs and queues · Queue health · Horizon monitoring" }] : []),
            ],
            onSelect: closeNavigation,
          },
        ]
      : []),
    {
      label: "Settings",
      to: "/settings",
      icon: "i-ph-gear-six",
      defaultOpen: true,
      type: "trigger" as const,
      children: [
        {
          label: "Profile",
          to: "/settings/profile",
          exact: true,
          searchDescription: "Profile settings",
          onSelect: closeNavigation,
        },
        {
          label: "Security",
          to: "/settings/security",
          searchDescription: "Security settings · Password · Two-factor authentication · Account",
          onSelect: () => {
            open.value = false;
          },
        },
      ],
    },
  ],
]);

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

const groups = computed(() => [
  {
    id: "links",
    label: "Go to",
    items: links.value.flatMap((group) => navigationSearchItems(group)),
  },
]);
</script>

<template>
  <Suspense>
    <UDashboardGroup storage="local" unit="rem">
      <UDashboardSidebar id="default" v-model:open="open" :ui="{ footer: 'lg:border-t lg:border-default' }" class="bg-elevated/25" collapsible resizable>
        <template #header="{ collapsed }">
          <TeamsMenu :collapsed="collapsed" />
        </template>

        <template #default="{ collapsed }">
          <UDashboardSearchButton :collapsed="collapsed" class="bg-transparent ring-default" />

          <UNavigationMenu :collapsed="collapsed" :items="links[0]" orientation="vertical" popover tooltip />

          <UNavigationMenu :collapsed="collapsed" :items="links[1]" class="mt-auto" orientation="vertical" tooltip />
        </template>

        <template #footer="{ collapsed }">
          <UserMenu :collapsed="collapsed" />
        </template>
      </UDashboardSidebar>

      <UDashboardSearch :groups="groups" />

      <slot />
    </UDashboardGroup>
  </Suspense>
</template>
