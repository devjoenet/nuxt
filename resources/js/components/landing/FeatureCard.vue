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
  }

  const props = withDefaults(defineProps<Props>(), {
    centered: false,
  });

  const toneClasses = computed(() => {
    const tones = {
      primary: "text-primary-400 border-primary-500/30 bg-primary-500/10",
      accent: "text-accent-400 border-accent-500/30 bg-accent-500/10",
      secondary: "text-secondary-400 border-secondary-500/30 bg-secondary-500/10",
      warning: "text-warning-400 border-warning-500/30 bg-warning-500/10",
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

  const headingClasses = "text-muted-950 dark:text-neutral-50";
  const descriptionClasses = "text-muted-700 dark:text-neutral-300";
  const detailClasses = "text-muted-600 dark:text-neutral-400";
</script>

<template>
  <article
    :class="[articleClasses, { 'text-center': centered }]"
    class="group relative h-full overflow-hidden rounded-lg border bg-muted-50/90 p-6 shadow-xl shadow-muted-950/10 ring-1 ring-muted-200/70 transition hover:-translate-y-1 dark:bg-muted-900/70 dark:shadow-muted-950/20 dark:ring-neutral-100/5"
  >
    <div class="relative z-10">
      <div :class="centered ? 'justify-center' : 'justify-start'" class="flex">
        <div :class="toneClasses" class="flex size-12 items-center justify-center rounded-md border">
          <UIcon :name="item.icon" aria-hidden="true" class="size-6" />
        </div>
      </div>

      <h3 :class="headingClasses" class="mt-5 text-lg font-semibold">{{ item.title }}</h3>
      <p :class="descriptionClasses" class="mt-3 text-sm leading-6">{{ item.description }}</p>
      <p :class="detailClasses" class="mt-1 text-sm leading-6">{{ item.detail }}</p>
      <p v-if="proof" class="mt-5 border-t border-muted-200 pt-4 text-xs leading-5 text-muted-600 dark:border-neutral-100/10 dark:text-neutral-400">{{ proof }}</p>
    </div>
  </article>
</template>
