<script lang="ts" setup>
import { computed } from "vue";
import { cn } from "@/lib/utils";
import type { IconProps } from "./types";
import { iconVariants } from "./variants";

const props = withDefaults(defineProps<IconProps>(), { tone: "current", size: "md" });
const isDecorative = computed(() => props.decorative ?? !props.label);
const iconClass = computed(() => cn(iconVariants({ tone: props.tone, size: props.size }), props.class));
</script>

<template><UIcon :aria-hidden="isDecorative || undefined" :aria-label="isDecorative ? undefined : label" :class="iconClass" :name="name" :role="isDecorative ? undefined : 'img'" /></template>
