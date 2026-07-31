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

const auth = useAuth();

const open = ref(false);

function closeNavigation(): void {
  open.value = false;
}

const links = computed<NavigationMenuItem[][]>(() => [
  [
    {
      label: "Home",
      icon: "i-ph-house",
      to: "/dashboard",
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
              { label: "Leads", to: leadsIndex.url() },
              { label: "Organizations", to: organizationsIndex.url() },
              { label: "Contacts", to: contactsIndex.url() },
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
              { label: "Overview", to: developmentIndex.url(), exact: true },
              ...(auth.value.user?.can?.["development.requests.create"]
                ? [
                    { label: "My requests", to: developmentRequestsIndex.url(), exact: true },
                    { label: "Submit request", to: developmentRequestCreate.url() },
                  ]
                : []),
              ...(auth.value.user?.can?.["development.requests.review"] ? [{ label: "Review queue", to: developmentReviewIndex.url() }] : []),
              ...(auth.value.user?.can?.["development.roadmap.manage"] ? [{ label: "Roadmap", to: roadmapIndex.url() }] : []),
              ...(auth.value.user?.can?.["development.user_testing.manage"] || auth.value.user?.has_pending_development_tests ? [{ label: "User testing", to: userTestingIndex.url() }] : []),
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
              { label: "Overview", to: "/admin", exact: true },
              ...(auth.value.user?.can?.["admin.users.manage"] ? [{ label: "Users", to: "/admin/users" }] : []),
              ...(auth.value.user?.can?.["admin.roles.manage"] ? [{ label: "Roles", to: "/admin/roles" }] : []),
              ...(auth.value.user?.can?.["audit.view"] ? [{ label: "Audit log", to: "/admin/audit" }] : []),
              ...(auth.value.user?.can?.["settings.manage"] ? [{ label: "Business settings", to: "/admin/settings" }] : []),
              ...(auth.value.user?.can?.["jobs.view"] ? [{ label: "Jobs & queues", to: jobsIndex.url() }] : []),
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
          onSelect: closeNavigation,
        },
        {
          label: "Security",
          to: "/settings/security",
          onSelect: () => {
            open.value = false;
          },
        },
      ],
    },
  ],
]);

const groups = computed(() => [
  {
    id: "links",
    label: "Go to",
    items: links.value.flat().map(({ chip: _chip, ui: _ui, ...item }): CommandPaletteItem => item),
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
