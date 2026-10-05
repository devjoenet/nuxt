<script lang="ts" setup>
import { Head } from "@inertiajs/vue3";
import type { DashboardToolbarProps } from "@nuxt/ui";
import type { ClassValue } from "clsx";
import { computed, useAttrs } from "vue";
import { WorkspaceFooter } from "@/components/WorkspaceFooter";
import { WorkspaceNavbar } from "@/components/WorkspaceNavbar";
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
    "flex w-full flex-col gap-4 pb-4 sm:gap-6 sm:pb-6",
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

      <WorkspaceNavbar :description-id="description ? descriptionId : undefined" :title="title">
        <template #actions><slot name="actions" /></template>
      </WorkspaceNavbar>

      <p v-if="description" :id="descriptionId" class="sr-only">{{ description }}</p>

      <UDashboardToolbar v-if="$slots.toolbar" :class="toolbarClass" :ui="toolbarUi">
        <slot name="toolbar" />
      </UDashboardToolbar>
    </template>

    <template #body>
      <div class="flex min-h-full flex-col">
        <main :class="contentClass" :data-content-width="contentWidth" data-workspace-page-content>
          <slot />
        </main>
        <WorkspaceFooter />
      </div>
    </template>
  </UDashboardPanel>
</template>
