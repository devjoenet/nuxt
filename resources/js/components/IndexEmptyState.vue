<script lang="ts" setup>
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
      <USkeleton class="h-5 w-40" />
      <USkeleton class="h-4 w-full max-w-md" />
      <USkeleton class="h-9 w-28" />
    </div>

    <UEmpty v-else :description="description" :icon="icon" :title="title">
      <template v-if="showReset || $slots.actions" #actions>
        <UButton v-if="showReset" :label="resetLabel" color="neutral" type="button" variant="soft" @click="$emit('reset')" />
        <slot name="actions" />
      </template>
    </UEmpty>
  </div>
</template>
