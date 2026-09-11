<script lang="ts" setup>
import { computed } from "vue";
import { cn } from "@/lib/utils";

const props = defineProps<{
  class?: string;
  contained?: boolean;
  loading?: boolean;
}>();

const innerClass = computed(() => (props.contained ? "inline-block min-w-full align-middle" : "inline-block min-w-full px-4 py-2 align-middle sm:px-6 lg:px-8"));
</script>

<template>
  <div :aria-busy="loading" :class="cn(contained ? 'overflow-x-auto whitespace-nowrap' : '-mx-4 -my-2 overflow-x-auto whitespace-nowrap sm:-mx-6 lg:-mx-8', props.class)" data-index-table-frame>
    <div :class="innerClass">
      <slot />
    </div>
  </div>
</template>
