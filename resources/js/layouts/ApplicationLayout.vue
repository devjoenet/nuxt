<script setup lang="ts">
import WorkspaceSearch from "@/components/WorkspaceSearch.vue";
import WorkspaceSidebar from "@/components/WorkspaceSidebar.vue";
import { useFlashToasts } from "@/composables/useFlashToasts";
import { useWorkspaceNavigation } from "@/composables/useWorkspaceNavigation";
import { ref } from "vue";

const navigationOpen = ref(false);
useFlashToasts();
const { navigationGroups, searchGroups } = useWorkspaceNavigation({
  onNavigate: () => {
    navigationOpen.value = false;
  },
});
</script>

<template>
  <Suspense>
    <UDashboardGroup class="bg-surface-canvas" storage="local" unit="rem">
      <WorkspaceSidebar v-model:open="navigationOpen" :navigation-groups="navigationGroups" />
      <WorkspaceSearch :groups="searchGroups" />
      <slot />
    </UDashboardGroup>
  </Suspense>
</template>
