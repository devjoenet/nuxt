<script lang="ts" setup>
import { computed } from "vue";
import { cn } from "@/lib/utils";
import { Icon } from "@/components/Ui/Icon";
import { Typography } from "@/components/Ui/Typography";
import type { EmptyStateProps, EmptyStateSlots } from "./types";
import { emptyStateVariants } from "./variants";

const props = withDefaults(defineProps<EmptyStateProps>(), { compact: false });
defineSlots<EmptyStateSlots>();
const emptyStateClass = computed(() => cn(emptyStateVariants({ compact: props.compact }), props.class));
</script>

<template>
  <div :class="emptyStateClass" role="status">
    <span v-if="icon" class="grid size-12 place-items-center rounded-full bg-primary-100 text-primary dark:bg-primary-950"><Icon decorative :name="icon" size="lg" /></span>
    <Typography as="h3" tone="heading" variant="h4">{{ title }}</Typography>
    <Typography v-if="description" class="max-w-prose" variant="small">{{ description }}</Typography>
    <div v-if="$slots.actions" class="mt-2 flex flex-wrap justify-center gap-2"><slot name="actions" /></div>
  </div>
</template>
