<script lang="ts" setup>
import { cn } from "@/lib/utils";

const props = withDefaults(
  defineProps<{
    title: string;
    description?: string;
    headingId?: string;
    as?: "h1" | "h2";
    class?: string;
  }>(),
  {
    as: "h2",
  },
);
</script>

<template>
  <div :class="cn('grid gap-1', props.class)">
    <component :is="as" :id="headingId" class="text-xl font-semibold text-balance text-highlighted">
      {{ title }}
    </component>
    <p v-if="description" class="text-base text-pretty text-muted sm:text-sm">
      {{ description }}
    </p>
  </div>
</template>
