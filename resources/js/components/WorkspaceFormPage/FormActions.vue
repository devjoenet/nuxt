<script lang="ts" setup>
import { cn } from "@/lib/utils";

const props = withDefaults(
  defineProps<{
    cancelTo: string;
    submitLabel: string;
    loading?: boolean;
    disabled?: boolean;
    cancelLabel?: string;
    submitIcon?: string;
    bordered?: boolean;
    class?: string;
  }>(),
  {
    cancelLabel: "Cancel",
    bordered: false,
  },
);
</script>

<template>
  <div :class="cn('flex flex-wrap justify-end gap-3', bordered && 'border-t border-default pt-5', props.class)">
    <UButton :label="cancelLabel" :to="cancelTo" color="neutral" variant="ghost" />
    <UButton :disabled="disabled" :icon="submitIcon" :label="submitLabel" :loading="loading" type="submit" />
  </div>
</template>
