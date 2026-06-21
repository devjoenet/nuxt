<script setup lang="ts">
  import { computed } from "vue";

  interface LandingCard {
    title: string;
    description: string;
    detail: string;
    icon: string;
    tone: "primary" | "accent" | "secondary" | "warning";
  }

  interface Props {
    item: LandingCard;
    proof?: string;
    centered?: boolean;
    liquid?: boolean;
    liquidActive?: boolean;
    liquidDelay?: number;
  }

  const props = withDefaults(defineProps<Props>(), {
    centered: false,
    liquid: false,
    liquidActive: false,
    liquidDelay: 0,
  });

  const toneClasses = computed(() => {
    const tones = {
      primary: props.liquidActive ? "text-primary-950 border-primary-400/80 bg-primary-400" : "text-primary-400 border-primary-500/30 bg-primary-500/10",
      accent: props.liquidActive ? "text-accent-950 border-accent-400/80 bg-accent-400" : "text-accent-400 border-accent-500/30 bg-accent-500/10",
      secondary: props.liquidActive ? "text-secondary-950 border-secondary-400/80 bg-secondary-400" : "text-secondary-400 border-secondary-500/30 bg-secondary-500/10",
      warning: props.liquidActive ? "text-warning-950 border-warning-400/80 bg-warning-400" : "text-warning-400 border-warning-500/30 bg-warning-500/10",
    };

    return tones[props.item.tone];
  });

  const articleClasses = computed(() => {
    const tones = {
      primary: "border-primary-500/15 hover:border-primary-500/35",
      accent: "border-accent-500/15 hover:border-accent-500/35",
      secondary: "border-secondary-500/15 hover:border-secondary-500/35",
      warning: "border-warning-500/15 hover:border-warning-500/35",
    };

    return tones[props.item.tone];
  });

  const overlayClasses = computed(() => {
    const tones = {
      primary: props.liquidActive ? "bg-primary-500/68 opacity-100" : "bg-primary-500/0 opacity-0",
      accent: props.liquidActive ? "bg-accent-500/68 opacity-100" : "bg-accent-500/0 opacity-0",
      secondary: props.liquidActive ? "bg-secondary-500/68 opacity-100" : "bg-secondary-500/0 opacity-0",
      warning: props.liquidActive ? "bg-warning-500/68 opacity-100" : "bg-warning-500/0 opacity-0",
    };

    return tones[props.item.tone];
  });

  const headingClasses = computed(() => {
    if (!props.liquidActive) {
      return "text-muted-950 dark:text-neutral-50";
    }

    const tones = {
      primary: "text-primary-950",
      accent: "text-accent-50",
      secondary: "text-secondary-50",
      warning: "text-warning-950",
    };

    return tones[props.item.tone];
  });

  const descriptionClasses = computed(() => {
    if (!props.liquidActive) {
      return "text-muted-700 dark:text-neutral-300";
    }

    const tones = {
      primary: "text-primary-950/85",
      accent: "text-accent-50/85",
      secondary: "text-secondary-50/85",
      warning: "text-warning-950/85",
    };

    return tones[props.item.tone];
  });

  const detailClasses = computed(() => {
    if (!props.liquidActive) {
      return "text-muted-600 dark:text-neutral-400";
    }

    const tones = {
      primary: "text-primary-950/75",
      accent: "text-accent-50/75",
      secondary: "text-secondary-50/75",
      warning: "text-warning-950/75",
    };

    return tones[props.item.tone];
  });

  const liquidDelayStyle = computed(() => ({
    "--feature-liquid-delay": `${props.liquidDelay}ms`,
  }));
</script>

<template>
  <article
    :class="[articleClasses, { 'text-center': centered }]"
    :style="liquidDelayStyle"
    class="group relative h-full overflow-hidden rounded-lg border bg-muted-50/90 p-6 shadow-xl shadow-muted-950/10 ring-1 ring-muted-200/70 transition hover:-translate-y-1 hover:bg-muted-100 dark:bg-muted-900/70 dark:shadow-muted-950/20 dark:ring-neutral-100/5 dark:hover:bg-muted-900"
  >
    <span v-if="liquid" :class="[overlayClasses, { 'is-active': liquidActive }]" aria-hidden="true" class="feature-liquid-fill absolute -inset-6 z-0"></span>

    <div class="relative z-10">
      <div :class="centered ? 'justify-center' : 'justify-start'" class="flex">
        <div :class="toneClasses" class="feature-liquid-content flex size-12 items-center justify-center rounded-md border transition duration-[1800ms]">
          <UIcon :name="item.icon" aria-hidden="true" class="size-6" />
        </div>
      </div>

      <h3 :class="headingClasses" class="feature-liquid-content mt-5 text-lg font-semibold transition duration-[1800ms]">{{ item.title }}</h3>
      <p :class="descriptionClasses" class="feature-liquid-content mt-3 text-sm leading-6 transition duration-[1800ms]">{{ item.description }}</p>
      <p :class="detailClasses" class="feature-liquid-content mt-1 text-sm leading-6 transition duration-[1800ms]">{{ item.detail }}</p>
      <p v-if="proof" class="mt-5 border-t border-muted-200 pt-4 text-xs leading-5 text-muted-600 dark:border-neutral-100/10 dark:text-neutral-400">{{ proof }}</p>
    </div>
  </article>
</template>

<style scoped>
  .feature-liquid-fill {
    border-radius: 50% 20% 100% 100%;
    box-shadow: inset 20px -9px 5px 0 rgb(10 10 10 / 12%);
    transform: translateY(-112%) rotate(-130deg) scale(1);
    transform-origin: center;
    transition-delay: var(--feature-liquid-delay, 0ms);
    transition-duration: 1200ms, 1700ms, 1700ms, 1200ms, 1200ms;
    transition-property: background-color, transform, border-radius, box-shadow, opacity;
    transition-timing-function: ease-out;
  }

  .feature-liquid-fill.is-active {
    border-radius: 0;
    box-shadow: inset 20px -9px 8px 0 rgb(10 10 10 / 18%);
    transform: translateY(0) rotate(0deg) scale(1.55);
    transition-duration: 900ms, 1500ms, 1500ms, 900ms, 900ms;
  }

  .feature-liquid-content {
    transition-delay: var(--feature-liquid-delay, 0ms);
  }

  @media (prefers-reduced-motion: reduce) {
    .feature-liquid-fill,
    .feature-liquid-content {
      transition-delay: 0ms;
      transition-duration: 0ms;
    }
  }
</style>
