<script setup lang="ts">
defineProps<{ href: string }>();
</script>

<template>
  <UButton :to="href" color="neutral" icon="i-ph-clock-counter-clockwise" label="History" variant="outline" />
</template>
