<script lang="ts" setup>
import { computed } from "vue";
import { cn } from "@/lib/utils";
import type { ProgressProps } from "./types";
import { progressVariants } from "./variants";

const props = withDefaults(defineProps<ProgressProps>(), { max: 100, tone: "primary", size: "md", showValue: false });
const percent = computed(() => (props.value === undefined ? undefined : Math.min(100, Math.max(0, (props.value / props.max) * 100))));
const progressClass = computed(() => cn(progressVariants({ tone: props.tone, size: props.size }), props.class));
</script>

<template>
  <div>
    <div v-if="label || showValue" class="mb-2 flex items-center justify-between gap-3 text-sm">
      <span>{{ label }}</span
      ><span v-if="showValue && percent !== undefined">{{ Math.round(percent) }}%</span>
    </div>
    <div :aria-label="label" :aria-valuemax="max" :aria-valuemin="0" :aria-valuenow="value" :class="progressClass" role="progressbar">
      <div v-if="percent !== undefined" class="h-full rounded-[inherit] bg-[var(--ui-tone)] transition-[width] duration-500 ease-argon-standard" :style="{ width: `${percent}%` }" />
      <div v-else class="h-full w-1/2 animate-argon-pulse rounded-[inherit] bg-[var(--ui-tone)]" />
    </div>
  </div>
</template>
