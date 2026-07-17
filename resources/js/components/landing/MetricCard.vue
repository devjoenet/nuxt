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
      primary: "text-primary-400 border-primary-500/30 bg-primary-500/10 group-hover:border-primary-400/80 group-hover:bg-primary-400 group-hover:text-primary-950",
      accent: "text-accent-400 border-accent-500/30 bg-accent-500/10 group-hover:border-accent-400/80 group-hover:bg-accent-400 group-hover:text-accent-950",
      secondary: "text-secondary-400 border-secondary-500/30 bg-secondary-500/10 group-hover:border-secondary-400/80 group-hover:bg-secondary-400 group-hover:text-secondary-950",
    };

    return tones[props.metric.tone];
  });

  const articleClasses = computed(() => {
    const tones = {
      primary: "border-primary-500/15 hover:border-primary-500/35",
      accent: "border-accent-500/15 hover:border-accent-500/35",
      secondary: "border-secondary-500/15 hover:border-secondary-500/35",
    };

    return tones[props.metric.tone];
  });

  const overlayClasses = computed(() => {
    const tones = {
      primary: "bg-primary-500/35 group-hover:bg-primary-500/95",
      accent: "bg-accent-500/35 group-hover:bg-accent-500/95",
      secondary: "bg-secondary-500/35 group-hover:bg-secondary-500/95",
    };

    return tones[props.metric.tone];
  });

  const valueClasses = computed(() => {
    const tones = {
      primary: "text-primary-400 group-hover:text-primary-950",
      accent: "text-accent-400 group-hover:text-accent-50",
      secondary: "text-secondary-400 group-hover:text-secondary-50",
    };

    return tones[props.metric.tone];
  });

  const copyClasses = computed(() => {
    const tones = {
      primary: "group-hover:text-primary-950/80",
      accent: "group-hover:text-accent-50/85",
      secondary: "group-hover:text-secondary-50/85",
    };

    return tones[props.metric.tone];
  });
</script>

<template>
  <motion.article
    :initial="{ opacity: 0, y: 20 }"
    :transition="{ duration: 0.65, ease: 'easeOut' }"
    :viewport="{ once: true, amount: 0.3 }"
    :while-in-view="{ opacity: 1, y: 0 }"
    :class="articleClasses"
    class="group relative h-full overflow-hidden rounded-lg border bg-muted-50/90 p-6 shadow-xl shadow-muted-950/10 ring-1 ring-muted-200/70 transition motion-safe:hover:-translate-y-1 dark:bg-muted-900/70 dark:shadow-muted-950/20 dark:ring-neutral-100/5"
  >
    <span :class="overlayClasses" aria-hidden="true" class="metric-liquid-fill absolute inset-0 z-0"></span>

    <div class="relative z-10">
      <div class="flex size-12 items-center justify-center rounded-md border transition duration-1000" :class="toneClasses">
        <UIcon :name="metric.icon" aria-hidden="true" class="size-6" />
      </div>

      <p :class="valueClasses" class="mt-5 text-4xl font-semibold transition duration-1000">{{ metric.value }}</p>
      <p :class="valueClasses" class="mt-1 text-lg font-bold tracking-wide uppercase transition duration-1000">
        {{ metric.label }}
      </p>
      <p :class="copyClasses" class="mt-3 text-sm leading-6 text-muted-600 transition duration-1000 dark:text-neutral-400">{{ metric.description }}</p>
    </div>
  </motion.article>
</template>

<style scoped>
  .metric-liquid-fill {
    border-radius: 50% 20% 100% 100%;
    box-shadow: inset 20px -9px 5px 0 rgb(10 10 10 / 12%);
    transform: translateY(-112%) rotate(-130deg) scale(1);
    transition-duration: 700ms, 1100ms, 1100ms, 700ms;
    transition-property: background-color, transform, border-radius, box-shadow;
    transition-timing-function: ease-out;
  }

  .group:hover .metric-liquid-fill {
    border-radius: 0;
    box-shadow: inset 20px -9px 8px 0 rgb(10 10 10 / 22%);
    transform: translateY(0) rotate(0deg) scale(1.55);
    transition-duration: 250ms, 900ms, 900ms, 250ms;
  }

  @media (prefers-reduced-motion: reduce) {
    .metric-liquid-fill {
      transition-duration: 0ms;
    }
  }
</style>
