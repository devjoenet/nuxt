<script setup lang="ts">
import WorkspaceSidebar from "@/components/WorkspaceSidebar.vue";
import WorkspaceSearch from "@/components/WorkspaceSearch.vue";
import { useWorkspaceNavigation } from "@/composables/useWorkspaceNavigation";
import { ref } from "vue";

const navigationOpen = ref(false);
const { navigationGroups, searchGroups } = useWorkspaceNavigation({
  onNavigate: () => {
    navigationOpen.value = false;
  },
});
</script>

<template>
  <Suspense>
    <UDashboardGroup storage="local" unit="rem">
      <WorkspaceSidebar v-model:open="navigationOpen" :navigation-groups="navigationGroups" />
      <WorkspaceSearch :groups="searchGroups" />
      <slot />
    </UDashboardGroup>
  </Suspense>
</template>
