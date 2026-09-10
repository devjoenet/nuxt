<script setup lang="ts">
import { Card } from "@/components/Ui/Card";
defineProps<{ items: { label: string; value: string; change?: string }[] }>();
</script>
<template>
  <dl class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    <Card v-for="item in items" :key="item.label" as="div" variant="outline">
      <dt class="text-sm text-content-subtle">{{ item.label }}</dt>
      <dd class="mt-2 text-2xl font-semibold tabular-nums text-content-heading">{{ item.value }}</dd>
      <dd v-if="item.change" class="mt-1 text-sm text-content-body">{{ item.change }}</dd>
    </Card>
  </dl>
</template>
