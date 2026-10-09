<script lang="ts" setup>
import { TeamsMenu } from "@/components/TeamsMenu";
import { UserMenu } from "@/components/UserMenu";
import type { SearchableNavigationItem } from "@/composables/useWorkspaceNavigation";

defineProps<{
  navigationGroups: SearchableNavigationItem[][];
}>();

const open = defineModel<boolean>("open", { default: false });
</script>

<template>
  <UDashboardSidebar
    id="default"
    v-model:open="open"
    :ui="{
      root: 'lg:h-svh lg:border-content-border',
      footer: 'lg:border-t lg:border-content-border',
    }"
    aria-label="Workspace sidebar"
    class="bg-surface-elevated"
    collapsible
    data-testid="workspace-sidebar"
    resizable
  >
    <template #header="{ collapsed }">
      <TeamsMenu :collapsed="collapsed" />
    </template>

    <template #default="{ collapsed }">
      <UDashboardSearchButton :collapsed="collapsed" class="bg-transparent ring-default" />

      <UNavigationMenu :collapsed="collapsed" :items="navigationGroups" :ui="{ content: collapsed ? undefined : 'data-[state=closed]:hidden' }" aria-label="Primary workspace navigation" orientation="vertical" popover tooltip />
    </template>

    <template #footer="{ collapsed }">
      <UserMenu :collapsed="collapsed" />
    </template>
  </UDashboardSidebar>
</template>
