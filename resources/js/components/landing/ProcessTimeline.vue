<script setup lang="ts">
  interface ProcessStep {
    title: string;
    description: string;
    tone: "primary" | "secondary" | "accent" | "warning";
  }

  defineProps<{
    steps: ProcessStep[];
  }>();

  const toneClasses = {
    primary: "border-brand-primary-400 text-brand-primary-300 shadow-brand-primary-500/25",
    secondary: "border-brand-secondary-400 text-brand-secondary-300 shadow-brand-secondary-500/25",
    accent: "border-brand-accent-400 text-brand-accent-300 shadow-brand-accent-500/25",
    warning: "border-brand-warning-400 text-brand-warning-300 shadow-brand-warning-500/25",
  };
</script>

<template>
  <ol class="mt-10 grid gap-6 md:grid-cols-4">
    <li v-for="(step, index) in steps" :key="step.title" class="relative">
      <div
        v-if="index < steps.length - 1"
        class="absolute top-6 left-14 hidden h-px w-[calc(100%-2.5rem)] border-t border-dashed border-brand-primary-400/60 md:block"
        aria-hidden="true"
      ></div>

      <article class="relative rounded-lg border border-transparent bg-muted-950/30 p-5">
        <div class="flex size-12 items-center justify-center rounded-full border bg-muted-950 text-lg font-semibold shadow-lg" :class="toneClasses[step.tone]">
          {{ index + 1 }}
        </div>

        <h3 class="mt-5 text-base font-semibold text-neutral-50">{{ step.title }}</h3>
        <p class="mt-2 text-sm leading-6 text-neutral-400">{{ step.description }}</p>
      </article>
    </li>
  </ol>
</template>
