<script lang="ts" setup>
import { computed } from "vue";
import { cn } from "@/lib/utils";
import type { DividerProps, DividerSlots } from "./types";
import { dividerVariants } from "./variants";

const props = withDefaults(defineProps<DividerProps>(), { orientation: "horizontal", decorative: false });
defineSlots<DividerSlots>();
const dividerClass = computed(() => cn(dividerVariants({ orientation: props.orientation }), props.class));
</script>

<template>
  <div v-if="label && orientation === 'horizontal'" :class="cn('flex items-center gap-3 text-xs font-semibold tracking-wider text-content-subtle uppercase', props.class)" :role="decorative ? undefined : 'separator'">
    <span class="h-px flex-1 bg-content-divider" /><span
      ><slot>{{ label }}</slot></span
    ><span class="h-px flex-1 bg-content-divider" />
  </div>
  <div v-else :aria-hidden="decorative || undefined" :aria-orientation="orientation" :class="dividerClass" :role="decorative ? undefined : 'separator'" />
</template>
