<script lang="ts" setup>
import { computed } from "vue";
import { cn } from "@/lib/utils";
import { Spinner } from "@/components/Ui/Spinner";
import type { ButtonProps, ButtonSlots } from "./types";
import { buttonVariants } from "./variants";

defineOptions({ inheritAttrs: false });
defineSlots<ButtonSlots>();

const props = withDefaults(defineProps<ButtonProps>(), {
  tone: "primary",
  variant: "solid",
  size: "md",
  shape: "rounded",
  type: "button",
  disabled: false,
  loading: false,
  block: false,
});
const emit = defineEmits<{ click: [event: MouseEvent] }>();
const buttonClass = computed(() => cn(buttonVariants({ tone: props.tone, variant: props.variant, size: props.size, shape: props.shape, block: props.block }), props.class));
</script>

<template>
  <button :aria-busy="loading || undefined" :class="buttonClass" :disabled="disabled || loading" :type="type" v-bind="$attrs" @click="emit('click', $event)">
    <Spinner v-if="loading" decorative size="sm" />
    <slot v-else name="leading" :loading="loading" />
    <span v-if="label || $slots.default"
      ><slot :loading="loading">{{ label }}</slot></span
    >
    <slot name="trailing" :loading="loading" />
  </button>
</template>
