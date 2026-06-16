<script setup lang="ts">
  import { computed, type Component } from "vue";

  interface MetricItem {
    value: string;
    label: string;
    description: string;
    icon: Component;
    tone: "primary" | "accent";
  }

  const props = defineProps<{
    metric: MetricItem;
  }>();

  const toneClasses = computed(() => {
    if (props.metric.tone === "accent") {
      return "text-brand-accent-300 border-brand-accent-400/25 bg-brand-accent-500/5";
    }

    return "text-brand-primary-300 border-brand-primary-400/25 bg-brand-primary-500/5";
  });
</script>

<template>
  <article class="h-full rounded-lg border border-brand-primary-400/15 bg-muted-900/70 p-6 shadow-xl shadow-muted-950/20 ring-1 ring-neutral-100/5">
    <div class="flex size-12 items-center justify-center rounded-md border" :class="toneClasses">
      <component :is="metric.icon" class="size-6" aria-hidden="true" />
    </div>

    <p class="mt-5 text-4xl font-semibold tracking-tight text-brand-primary-300">{{ metric.value }}</p>
    <p class="mt-1 text-lg font-bold tracking-wide uppercase" :class="metric.tone === 'accent' ? 'text-brand-accent-300' : 'text-brand-primary-300'">
      {{ metric.label }}
    </p>
    <p class="mt-3 text-sm leading-6 text-neutral-400">{{ metric.description }}</p>
  </article>
</template>
