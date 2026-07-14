<script setup lang="ts">
  import type { NavigationMenuItem } from "@nuxt/ui";

  const toast = useToast();
  const auth = useAuth();

  const open = ref(false);

  const links = computed<NavigationMenuItem[][]>(() => [
    [
      {
        label: "Home",
        icon: "i-ph-house",
        to: "/dashboard",
        onSelect: () => {
          open.value = false;
        },
      },
      {
        label: "Inbox",
        icon: "i-ph-tray",
        to: "/inbox",
        onSelect: () => {
          open.value = false;
        },
      },
      {
        label: "CRM",
        icon: "i-ph-users",
        to: "/crm/leads",
        onSelect: () => {
          open.value = false;
        },
      },
      ...(auth.value.user?.can?.["admin.dashboard.view"]
        ? [
            {
              label: "Admin",
              icon: "i-ph-shield-check",
              to: "/admin",
              type: "trigger",
              defaultOpen: true,
              children: [
                { label: "Overview", to: "/admin" },
                ...(auth.value.user?.can?.["admin.users.manage"] ? [{ label: "Users", to: "/admin/users" }] : []),
                ...(auth.value.user?.can?.["admin.roles.manage"] ? [{ label: "Roles", to: "/admin/roles" }] : []),
                ...(auth.value.user?.can?.["audit.view"] ? [{ label: "Audit log", to: "/admin/audit" }] : []),
                ...(auth.value.user?.can?.["settings.manage"] ? [{ label: "Business settings", to: "/admin/settings" }] : []),
              ],
              onSelect: () => {
                open.value = false;
              },
            },
          ]
        : []),
      {
        label: "Settings",
        to: "/settings",
        icon: "i-ph-gear-six",
        defaultOpen: true,
        type: "trigger",
        children: [
          {
            label: "Profile",
            to: "/settings/profile",
            exact: true,
            onSelect: () => {
              open.value = false;
            },
          },
          {
            label: "Members",
            to: "/settings/members",
            onSelect: () => {
              open.value = false;
            },
          },
          {
            label: "Notifications",
            to: "/settings/notifications",
            onSelect: () => {
              open.value = false;
            },
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
      items: links.value.flat(),
    },
  ]);

  const cookie = useStorage("cookie-consent", "pending");
  if (cookie.value !== "accepted") {
    toast.add({
      title: "We use first-party cookies to enhance your experience on our website.",
      duration: 0,
      close: false,
      actions: [
        {
          label: "Accept",
          color: "neutral",
          variant: "outline",
          onClick: () => {
            cookie.value = "accepted";
          },
        },
        {
          label: "Opt out",
          color: "neutral",
          variant: "ghost",
        },
      ],
    });
  }
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

      <NotificationsSlideover />
    </UDashboardGroup>
  </Suspense>
</template>
