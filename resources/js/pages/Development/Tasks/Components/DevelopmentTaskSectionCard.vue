<script setup lang="ts">
import { Card } from "@/components/Ui/Card";

const props = defineProps<{
  headingId: string;
  eyebrow: string;
  title: string;
  description: string;
  tone: "primary" | "secondary" | "neutral" | "success" | "info" | "warning" | "error";
  class?: string;
}>();
</script>

<template>
  <Card :aria-labelledby="headingId" :class="props.class" padding="none" :tone="tone" variant="outline">
    <header class="grid gap-3 border-b border-default p-5 sm:p-6">
      <UBadge :color="tone" :label="eyebrow" class="w-fit font-mono tracking-wide text-highlighted uppercase" variant="soft" />
      <div class="grid gap-2">
        <h2 :id="headingId" class="max-w-[40ch] text-2xl font-semibold tracking-tight text-balance text-highlighted">{{ title }}</h2>
        <p class="max-w-[72ch] text-base/7 text-pretty text-muted sm:text-sm/6">{{ description }}</p>
      </div>
    </header>

    <div class="grid gap-6 p-5 sm:p-6"><slot /></div>
  </Card>
</template>
