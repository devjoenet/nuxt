<script lang="ts" setup>
import { computed } from "vue";
import { cn } from "@/lib/utils";
import type { ButtonGroupProps, ButtonGroupSlots } from "./types";
import { buttonGroupVariants } from "./variants";

const props = withDefaults(defineProps<ButtonGroupProps>(), {
  orientation: "horizontal",
  attached: false,
});
defineSlots<ButtonGroupSlots>();
const groupClass = computed(() => cn(buttonGroupVariants({ orientation: props.orientation, attached: props.attached }), props.class));
</script>

<template>
  <div :aria-label="label" :aria-orientation="orientation" :class="groupClass" role="group"><slot /></div>
</template>
