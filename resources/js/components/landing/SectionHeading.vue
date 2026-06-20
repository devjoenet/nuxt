<script setup lang="ts">
  import { computed } from "vue";

  interface Props {
    kicker: string;
    title: string;
    description?: string;
    tone?: "primary" | "secondary" | "accent";
  }

  const props = withDefaults(defineProps<Props>(), {
    tone: "primary",
  });

  const toneClasses = computed(() => {
    const tones = {
      primary: "text-primary-400",
      secondary: "text-secondary-400",
      accent: "text-accent-400",
    };

    return tones[props.tone];
  });
</script>

<template>
  <div class="mx-auto max-w-3xl text-center">
    <p :class="toneClasses" class="text-xs font-bold tracking-[0.22em] uppercase">{{ kicker }}</p>
    <h2 class="mt-3 text-3xl leading-tight font-semibold text-muted-950 sm:text-4xl dark:text-neutral-50">{{ title }}</h2>
    <p v-if="description" class="mt-4 text-base leading-7 text-muted-700 dark:text-neutral-300">{{ description }}</p>
  </div>
</template>
