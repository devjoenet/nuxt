<script lang="ts" setup>
import { computed } from "vue";
import { cn } from "@/lib/utils";
import type { CardProps, CardSlots } from "./types";
import { cardVariants } from "./variants";

const props = withDefaults(defineProps<CardProps>(), {
  as: "article",
  tone: "neutral",
  variant: "elevated",
  padding: "md",
  interactive: false,
});
defineSlots<CardSlots>();
const cardClass = computed(() => cn(cardVariants({ tone: props.tone, variant: props.variant, padding: props.padding, interactive: props.interactive }), props.class));
</script>

<template>
  <component :is="as" :class="cardClass" :data-tone="tone" :data-variant="variant">
    <header v-if="$slots.header" class="mb-4"><slot name="header" /></header>
    <slot />
    <footer v-if="$slots.footer" class="mt-4"><slot name="footer" /></footer>
  </component>
</template>
