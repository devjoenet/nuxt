<script setup lang="ts">
import { index as galleryIndex } from "@/routes/admin/argonGallery";
import type { NavigationMenuItem } from "@nuxt/ui";
import { computed } from "vue";

const props = withDefaults(
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

const items = computed<NavigationMenuItem[][]>(() =>
  props.navigationItems.length
    ? props.navigationItems
    : [
        [
          { label: "Overview", to: galleryIndex.url(), exact: true },
          { label: "Laboratory", to: `${galleryIndex.url()}#component-laboratory` },
          { label: "Manifest", to: `${galleryIndex.url()}#manifest-index` },
        ],
      ],
);
</script>

<template>
  <WorkspacePage id="component-gallery" :document-title="documentTitle" content-width="wide" :title="title">
    <template #toolbar>
      <UNavigationMenu :items="items" class="-mx-1 flex-1 overflow-x-auto" highlight />
    </template>
    <slot />
  </WorkspacePage>
</template>
