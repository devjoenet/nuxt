<script lang="ts" setup>
import { Head } from "@inertiajs/vue3";
import type { DashboardToolbarProps } from "@nuxt/ui";
import type { ClassValue } from "clsx";
import { computed, useAttrs } from "vue";
import { cn } from "@/lib/utils";

defineOptions({ inheritAttrs: false });

type ContentWidth = "narrow" | "medium" | "wide" | "full";

const props = withDefaults(
  defineProps<{
    id: string;
    title: string;
    documentTitle?: string;
    description?: string;
    contentWidth?: ContentWidth;
    contentClass?: string;
    toolbarClass?: DashboardToolbarProps["class"];
    toolbarUi?: DashboardToolbarProps["ui"];
  }>(),
  {
    contentWidth: "full",
  },
);

const attrs = useAttrs();
const panelAttrs = computed(() => {
  const { class: _class, ...panelAttrs } = attrs;

  return panelAttrs;
});
const panelClass = computed(() => cn(attrs.class as ClassValue));
const descriptionId = computed(() => `${props.id}-description`);
const contentClass = computed(() =>
  cn(
    "flex w-full flex-col gap-4 sm:gap-6",
    {
      "mx-auto max-w-2xl": props.contentWidth === "narrow",
      "mx-auto max-w-3xl": props.contentWidth === "medium",
      "mx-auto max-w-6xl": props.contentWidth === "wide",
    },
    props.contentClass,
  ),
);
</script>

<template>
  <UDashboardPanel :id="id" :class="panelClass" v-bind="panelAttrs">
    <template #header>
      <Head :title="documentTitle ?? title" />

      <UDashboardNavbar :aria-describedby="description ? descriptionId : undefined" :title="title">
        <template #leading><UDashboardSidebarCollapse /></template>
        <template #right><slot name="actions" /></template>
      </UDashboardNavbar>

      <p v-if="description" :id="descriptionId" class="sr-only">{{ description }}</p>

      <UDashboardToolbar v-if="$slots.toolbar" :class="toolbarClass" :ui="toolbarUi">
        <slot name="toolbar" />
      </UDashboardToolbar>
    </template>

    <template #body>
      <main :class="contentClass" :data-content-width="contentWidth" data-workspace-page-content>
        <slot />
      </main>
    </template>
  </UDashboardPanel>
</template>
