<script lang="ts" setup>
import type { ClassValue } from "clsx";
import { computed } from "vue";
import { cardAppearanceClass, type CardVariant, type PageCardStyle } from "@/lib/cardAppearance";
import { cn } from "@/lib/utils";

defineOptions({ inheritAttrs: false });

const props = withDefaults(
  defineProps<{
    style?: PageCardStyle;
    variant?: CardVariant;
    class?: ClassValue;
  }>(),
  {
    style: "subtle",
    variant: "neutral",
  },
);

const cardClass = computed(() => cn("app-card", cardAppearanceClass(props.style, props.variant), props.class));
</script>

<template>
  <UPageCard :class="cardClass" :data-card-style="props.style" :data-card-variant="props.variant" :variant="props.style" v-bind="$attrs">
    <template v-if="$slots.header" #header>
      <slot name="header" />
    </template>
    <template v-if="$slots.leading" #leading="slotProps">
      <slot name="leading" v-bind="slotProps" />
    </template>
    <template v-if="$slots.body" #body>
      <slot name="body" />
    </template>
    <template v-if="$slots.title" #title>
      <slot name="title" />
    </template>
    <template v-if="$slots.description" #description>
      <slot name="description" />
    </template>
    <template v-if="$slots.footer" #footer>
      <slot name="footer" />
    </template>
    <slot />
  </UPageCard>
</template>
