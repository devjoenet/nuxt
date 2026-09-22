<script setup lang="ts">
import { cn } from "@/lib/utils";

const props = defineProps<{
  loading: boolean;
  cancelLabel: string;
  class?: string;
}>();

const emit = defineEmits<{
  cancel: [];
}>();
</script>

<template>
  <div :class="cn('contents', props.class)">
    <UButton :loading="loading" class="min-h-12 text-base sm:min-h-7 sm:text-xs" color="neutral" label="Save" size="xs" type="submit" variant="soft" />
    <UButton :aria-label="cancelLabel" class="relative min-h-12 min-w-12 sm:min-h-7 sm:min-w-7" color="neutral" icon="i-ph-x" size="xs" type="button" variant="ghost" @click="emit('cancel')">
      <span aria-hidden="true" class="absolute top-1/2 left-1/2 size-[max(100%,3rem)] -translate-1/2 pointer-fine:hidden" />
    </UButton>
  </div>
</template>
