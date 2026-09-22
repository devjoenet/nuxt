<script setup lang="ts">
import { cn } from "@/lib/utils";

const props = withDefaults(
  defineProps<{
    label: string;
    editable?: boolean;
    editing?: boolean;
    editLabel?: string;
    valueClass?: string;
    class?: string;
  }>(),
  {
    editable: false,
    editing: false,
    editLabel: "Edit value",
    valueClass: "text-base/7 text-muted sm:text-sm/6",
  },
);

const emit = defineEmits<{
  edit: [];
}>();
</script>

<template>
  <div :class="cn('grid min-w-0 gap-1 border-s border-default ps-4', props.class)">
    <dt class="flex min-w-0 items-start justify-between gap-3 font-medium text-toned">
      {{ label }}
      <UButton
        v-if="editable && !editing"
        :aria-label="editLabel"
        :aria-pressed="editing"
        class="relative -m-2 min-h-12 min-w-12 shrink-0 justify-center sm:m-0 sm:min-h-7 sm:min-w-7"
        color="neutral"
        icon="i-ph-pencil-simple"
        size="xs"
        type="button"
        variant="ghost"
        @click="emit('edit')"
      >
        <span aria-hidden="true" class="absolute top-1/2 left-1/2 size-[max(100%,3rem)] -translate-1/2 pointer-fine:hidden" />
      </UButton>
    </dt>
    <dd :class="editing ? undefined : valueClass">
      <slot v-if="!editing" />
      <slot v-else name="editor" />
    </dd>
  </div>
</template>
