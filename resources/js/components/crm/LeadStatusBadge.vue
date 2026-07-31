<script lang="ts" setup>
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
    })[props.status] ?? "neutral") as "info" | "warning" | "primary" | "secondary" | "success" | "error" | "neutral",
);
</script>

<template>
  <UBadge :color="color" class="capitalize" variant="subtle">{{ status }}</UBadge>
</template>
