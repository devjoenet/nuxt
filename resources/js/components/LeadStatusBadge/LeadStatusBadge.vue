<script lang="ts" setup>
import { Badge } from "@/components/Ui/Badge";
import type { UiTone } from "@/lib/uiTone";

const props = defineProps<{ status: string }>();
const color = computed(
  () =>
    (({
      new: "info",
      contacted: "warning",
      qualified: "primary",
      proposal: "secondary",
      won: "success",
      lost: "error",
    })[props.status] ?? "neutral") as UiTone,
);
</script>

<template>
  <Badge :tone="color" class="capitalize" variant="soft">{{ status }}</Badge>
</template>
