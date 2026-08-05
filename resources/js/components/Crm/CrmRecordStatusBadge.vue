<script lang="ts" setup>
import { cn } from "@/lib/utils";

defineOptions({ inheritAttrs: false });

const props = defineProps<{
  status: "archived" | "deleted";
  label?: string;
  class?: string;
}>();
</script>

<template>
  <UBadge :class="cn(props.class)" :label="label ?? (status === 'archived' ? 'Archived' : 'Deleted')" color="warning" v-bind="$attrs" variant="subtle" />
</template>
