<script lang="ts" setup>
import { computed } from "vue";
import { cn } from "@/lib/utils";
import { Icon } from "@/components/Ui/Icon";
import type { StatProps } from "./types";
import { statVariants } from "./variants";

const props = withDefaults(defineProps<StatProps>(), { trend: "flat", tone: "primary" });
const statClass = computed(() => cn(statVariants({ tone: props.tone }), props.class));
const trendClass = computed(() => ({ up: "text-success", down: "text-error", flat: "text-content-subtle" })[props.trend]);
</script>

<template>
  <article :class="statClass">
    <div class="flex items-start justify-between gap-4">
      <div class="min-w-0">
        <p class="text-sm text-content-subtle">{{ label }}</p>
        <p class="mt-1 text-2xl font-semibold text-content-heading">{{ value }}</p>
      </div>
      <span v-if="icon" class="grid size-11 place-items-center rounded-lg bg-[var(--ui-tone-soft)] text-[var(--ui-tone)]"><Icon decorative :name="icon" size="lg" /></span>
    </div>
    <p v-if="delta || description" class="mt-3 text-sm">
      <span v-if="delta" :class="trendClass" class="font-semibold">{{ delta }}</span
      ><span v-if="description" class="ms-1 text-content-subtle">{{ description }}</span>
    </p>
  </article>
</template>
