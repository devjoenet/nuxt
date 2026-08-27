<script lang="ts" setup>
import { computed } from "vue";
import { cn } from "@/lib/utils";
import type { TypographyProps, TypographySlots } from "./types";
import { typographyVariants } from "./variants";

const props = withDefaults(defineProps<TypographyProps>(), { as: "p", variant: "body", tone: "body" });
defineSlots<TypographySlots>();
const typographyClass = computed(() => cn(typographyVariants({ variant: props.variant, tone: props.tone }), props.class));
</script>

<template>
  <component :is="as" :class="typographyClass"><slot /></component>
</template>
