<script lang="ts" setup>
import { computed } from "vue";
import { cn } from "@/lib/utils";
import type { SpinnerProps } from "./types";
import { spinnerVariants } from "./variants";

const props = withDefaults(defineProps<SpinnerProps>(), {
  label: "Loading",
  decorative: false,
  size: "md",
});

const spinnerClass = computed(() => cn(spinnerVariants({ size: props.size }), props.class));
</script>

<template>
  <span :aria-hidden="decorative || undefined" :aria-label="decorative ? undefined : label" :class="spinnerClass" :role="decorative ? undefined : 'status'" />
</template>
