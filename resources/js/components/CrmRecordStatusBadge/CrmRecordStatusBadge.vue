<script lang="ts" setup>
import { Badge } from "@/components/Ui/Badge";
import { cn } from "@/lib/utils";

defineOptions({ inheritAttrs: false });

const props = defineProps<{
  status: "archived" | "deleted";
  label?: string;
  class?: string;
}>();
</script>

<template>
  <Badge :class="cn(props.class)" :label="label ?? (status === 'archived' ? 'Archived' : 'Deleted')" tone="warning" v-bind="$attrs" variant="soft" />
</template>
