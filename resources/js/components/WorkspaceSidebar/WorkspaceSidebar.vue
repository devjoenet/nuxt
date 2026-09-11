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
      root: 'lg:m-3 lg:mr-0 lg:h-[calc(100svh-1.5rem)] lg:rounded-2xl lg:border lg:border-content-border lg:bg-surface-elevated lg:shadow-lg dark:lg:shadow-none',
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

      <UNavigationMenu :collapsed="collapsed" :items="navigationGroups[0]" aria-label="Primary workspace navigation" orientation="vertical" popover tooltip />

      <UNavigationMenu :collapsed="collapsed" :items="navigationGroups[1]" aria-label="Account navigation" class="mt-auto" orientation="vertical" popover tooltip />
    </template>

    <template #footer="{ collapsed }">
      <UserMenu :collapsed="collapsed" />
    </template>
  </UDashboardSidebar>
</template>
