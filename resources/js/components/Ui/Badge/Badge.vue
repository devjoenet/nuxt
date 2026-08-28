<script lang="ts" setup>
import { computed } from "vue";
import { cn } from "@/lib/utils";
import type { BadgeProps, BadgeSlots } from "./types";
import { badgeVariants } from "./variants";

const props = withDefaults(defineProps<BadgeProps>(), {
  tone: "primary",
  variant: "soft",
  size: "md",
  dot: false,
});
defineSlots<BadgeSlots>();
const badgeClass = computed(() => cn(badgeVariants({ tone: props.tone, variant: props.variant, size: props.size }), props.class));
</script>

<template>
  <span :class="badgeClass"
    ><span v-if="dot" aria-hidden="true" class="size-1.5 rounded-full bg-current" /><slot>{{ label }}</slot></span
  >
</template>
