<script lang="ts" setup>
import type { ClassValue } from "clsx";
import { computed } from "vue";
import { cardAppearanceClass, type CardStyle, type CardVariant } from "@/lib/cardAppearance";
import { cn } from "@/lib/utils";

defineOptions({ inheritAttrs: false });

const props = withDefaults(
  defineProps<{
    style?: CardStyle;
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
  <UCard :class="cardClass" :data-card-style="props.style" :data-card-variant="props.variant" :variant="props.style" v-bind="$attrs">
    <template v-if="$slots.header" #header>
      <slot name="header" />
    </template>
    <slot />
    <template v-if="$slots.footer" #footer>
      <slot name="footer" />
    </template>
  </UCard>
</template>
