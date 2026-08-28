<script setup lang="ts">
import type { NavigationMenuItem } from "@nuxt/ui";

withDefaults(
  defineProps<{
    documentTitle?: string;
    title?: string;
    navigationItems?: NavigationMenuItem[][];
  }>(),
  {
    documentTitle: "Component gallery",
    title: "Component gallery",
    navigationItems: () => [],
  },
);
</script>

<template>
  <WorkspacePage id="component-gallery" :document-title="documentTitle" content-width="wide" :title="title">
    <template v-if="navigationItems.length" #toolbar>
      <UNavigationMenu :items="navigationItems" class="-mx-1 flex-1" highlight />
    </template>
    <slot />
  </WorkspacePage>
</template>
