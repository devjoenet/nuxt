<script setup lang="ts">
  import { computed } from "vue";

  interface MetricItem {
    value: string;
    label: string;
    description: string;
    icon: string;
    tone: "primary" | "accent" | "secondary";
  }

  const props = defineProps<{
    metric: MetricItem;
  }>();

  const toneClasses = computed(() => {
    const tones = {
      primary: "text-primary-400 border-primary-500/30 bg-primary-500/10",
      accent: "text-accent-400 border-accent-500/30 bg-accent-500/10",
      secondary: "text-secondary-400 border-secondary-500/30 bg-secondary-500/10",
    };

    return tones[props.metric.tone];
  });

  const articleClasses = computed(() => {
    const tones = {
      primary: "border-primary-500/15",
      accent: "border-accent-500/15",
      secondary: "border-secondary-500/15",
    };

    return tones[props.metric.tone];
  });

  const valueClasses = computed(() => {
    const tones = {
      primary: "text-primary-400",
      accent: "text-accent-400",
      secondary: "text-secondary-400",
    };

    return tones[props.metric.tone];
  });
</script>

<template>
  <article
    :class="articleClasses"
    class="h-full rounded-lg border bg-muted-50/90 p-6 shadow-xl shadow-muted-950/10 ring-1 ring-muted-200/70 dark:bg-muted-900/70 dark:shadow-muted-950/20 dark:ring-neutral-100/5"
  >
    <div class="flex size-12 items-center justify-center rounded-md border" :class="toneClasses">
      <UIcon :name="metric.icon" aria-hidden="true" class="size-6" />
    </div>

    <p :class="valueClasses" class="mt-5 text-4xl font-semibold">{{ metric.value }}</p>
    <p :class="valueClasses" class="mt-1 text-lg font-bold tracking-wide uppercase">
      {{ metric.label }}
    </p>
    <p class="mt-3 text-sm leading-6 text-muted-600 dark:text-neutral-400">{{ metric.description }}</p>
  </article>
</template>
