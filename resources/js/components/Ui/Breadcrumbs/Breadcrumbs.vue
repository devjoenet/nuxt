<script lang="ts" setup>
import { computed } from "vue";
import { cn } from "@/lib/utils";
import type { BreadcrumbsProps, BreadcrumbsSlots } from "./types";
import { breadcrumbsVariants } from "./variants";

const props = withDefaults(defineProps<BreadcrumbsProps>(), { label: "Breadcrumb", density: "normal" });
defineSlots<BreadcrumbsSlots>();
const breadcrumbsClass = computed(() => cn(breadcrumbsVariants({ density: props.density }), props.class));
</script>

<template>
  <nav :aria-label="label" :class="breadcrumbsClass">
    <ol class="contents">
      <li v-for="(item, index) in items" :key="`${item.label}-${index}`" class="flex min-w-0 items-center gap-2">
        <span v-if="index" aria-hidden="true" class="text-content-subtle">/</span>
        <ULink v-if="item.href && !item.current" class="truncate text-primary hover:underline" :to="item.href"
          ><slot name="item" :item="item">{{ item.label }}</slot></ULink
        >
        <span v-else :aria-current="item.current ? 'page' : undefined" class="truncate font-semibold text-content-heading"
          ><slot name="item" :item="item">{{ item.label }}</slot></span
        >
      </li>
    </ol>
  </nav>
</template>
