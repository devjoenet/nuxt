<script lang="ts" setup>
import { Button } from "@/components/Ui/Button";
import { EmptyState } from "@/components/Ui/EmptyState";
import { Skeleton } from "@/components/Ui/Skeleton";
import { cn } from "@/lib/utils";

const props = withDefaults(
  defineProps<{
    class?: string;
    description: string;
    icon: string;
    loading?: boolean;
    resetLabel?: string;
    showReset?: boolean;
    title: string;
  }>(),
  {
    resetLabel: "Reset filters",
  },
);

defineEmits<{
  reset: [];
}>();
</script>

<template>
  <div :class="cn('min-w-0', props.class)">
    <div v-if="loading" aria-label="Loading results" class="grid gap-3 py-10" role="status">
      <Skeleton class="h-5 w-40" />
      <Skeleton class="h-4 w-full max-w-md" />
      <Skeleton class="h-9 w-28" />
    </div>

    <EmptyState v-else :description="description" :icon="icon" :title="title">
      <template v-if="showReset || $slots.actions" #actions>
        <Button v-if="showReset" :label="resetLabel" tone="neutral" type="button" variant="soft" @click="$emit('reset')" />
        <slot name="actions" />
      </template>
    </EmptyState>
  </div>
</template>
