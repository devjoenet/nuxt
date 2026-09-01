<script setup lang="ts">
import { AppearanceMenu } from "@/components/AppearanceMenu";
import { Breadcrumbs } from "@/components/Ui/Breadcrumbs";
import { useWorkspaceBreadcrumbs } from "@/composables/useWorkspaceBreadcrumbs";
import { toRef } from "vue";

const props = defineProps<{
  title: string;
  descriptionId?: string;
}>();

const breadcrumbs = useWorkspaceBreadcrumbs(toRef(props, "title"));
</script>

<template>
  <UDashboardNavbar :aria-describedby="descriptionId" class="border-b border-content-border bg-surface-translucent/95 backdrop-blur-argon" :ui="{ root: 'min-h-16 px-4 sm:px-6' }">
    <template #leading>
      <UDashboardSidebarCollapse />
    </template>

    <template #title>
      <Breadcrumbs :items="breadcrumbs" class="hidden sm:flex" density="compact" />
      <span class="truncate font-semibold text-content-heading sm:hidden">{{ title }}</span>
    </template>

    <template #right>
      <slot name="actions" />
      <AppearanceMenu />
    </template>
  </UDashboardNavbar>
</template>
