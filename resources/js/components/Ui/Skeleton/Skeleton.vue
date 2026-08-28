<script lang="ts" setup>
import { computed } from "vue";
import { cn } from "@/lib/utils";
import type { SkeletonProps } from "./types";
import { skeletonVariants } from "./variants";

const props = withDefaults(defineProps<SkeletonProps>(), { shape: "line", animated: true });
const skeletonClass = computed(() => cn(skeletonVariants({ shape: props.shape, animated: props.animated }), props.class));
</script>

<template><div aria-hidden="true" :class="skeletonClass" /></template>
