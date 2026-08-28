<script lang="ts" setup>
import { computed } from "vue";
import { cn } from "@/lib/utils";
import type { ListProps, ListSlots } from "./types";
import { listVariants } from "./variants";

const props = withDefaults(defineProps<ListProps>(), { ordered: false, variant: "plain", spacing: "normal" });
defineSlots<ListSlots>();
const listClass = computed(() => cn(listVariants({ variant: props.variant, spacing: props.spacing }), props.class));
</script>

<template>
  <component :is="ordered || variant === 'numbered' ? 'ol' : 'ul'" :class="listClass"><slot /></component>
</template>
