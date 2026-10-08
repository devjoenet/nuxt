<script setup lang="ts">
import { Motion } from "motion-v";

interface ProcessStep {
  title: string;
  description: string;
  icon: string;
  tone: "primary" | "secondary" | "accent" | "warning";
}

defineProps<{
  steps: ProcessStep[];
}>();

const circleClasses = {
  primary: "border-primary-400 text-primary-300 shadow-primary-500/30 group-hover:shadow-primary-400/45",
  secondary: "border-secondary-400 text-secondary-300 shadow-secondary-500/30 group-hover:shadow-secondary-400/45",
  accent: "border-brand-accent-400 text-brand-accent-300 shadow-brand-accent-500/30 group-hover:shadow-brand-accent-400/45",
  warning: "border-warning-400 text-warning-300 shadow-warning-500/30 group-hover:shadow-warning-400/45",
};

const cardClasses = {
  primary: "group-hover:border-primary-400/45 group-hover:shadow-primary-950/30",
  secondary: "group-hover:border-secondary-400/45 group-hover:shadow-secondary-950/30",
  accent: "group-hover:border-brand-accent-400/45 group-hover:shadow-brand-accent-950/30",
  warning: "group-hover:border-warning-400/45 group-hover:shadow-warning-950/30",
};

const labelClasses = {
  primary: "text-primary-300",
  secondary: "text-secondary-300",
  accent: "text-brand-accent-300",
  warning: "text-warning-300",
};

const railClasses = {
  primary: "from-primary-400/90 via-secondary-400/70 to-brand-accent-400/70",
  secondary: "from-secondary-400/90 via-brand-accent-400/70 to-warning-400/70",
  accent: "from-brand-accent-400/90 via-warning-400/70 to-primary-400/70",
  warning: "from-warning-400/90 via-primary-400/70 to-secondary-400/70",
};
</script>

<template>
  <ol class="mt-12 grid gap-5 md:grid-cols-4">
    <Motion
      as="li"
      v-for="(step, index) in steps"
      :key="step.title"
      :initial="{ opacity: 0, y: 26, scale: 0.97 }"
      :transition="{ duration: 0.65, delay: index * 0.12, ease: 'easeOut' }"
      :viewport="{ once: true, amount: 0.35 }"
      :while-in-view="{ opacity: 1, y: 0, scale: 1 }"
      class="group relative"
    >
      <div class="relative h-20">
        <div v-if="index < steps.length - 1" :class="railClasses[step.tone]" aria-hidden="true" class="absolute top-1/2 left-10 hidden h-px w-[calc(100%+1.25rem)] -translate-y-1/2 bg-linear-to-r md:block"></div>
        <div v-if="index < steps.length - 1" aria-hidden="true" class="absolute top-1/2 left-[4.8rem] hidden size-1.5 -translate-y-1/2 rounded-full bg-grey-100/45 shadow-[0_0_18px_color-mix(in_oklab,var(--color-grey-50)_32%,transparent)] md:block"></div>

        <div
          :class="circleClasses[step.tone]"
          class="relative z-10 flex size-20 items-center justify-center rounded-full border bg-slate-50/95 text-xl font-semibold shadow-2xl ring-1 ring-slate-200/80 transition duration-300 group-hover:-translate-y-1 group-hover:scale-105 dark:bg-slate-950/95 dark:ring-grey-100/10"
        >
          <span aria-hidden="true" class="absolute inset-2 rounded-full border border-slate-200 bg-(image:--app-landing-timeline-highlight) dark:border-grey-100/10"></span>
          <span class="relative flex flex-col items-center gap-0.5">
            <UIcon :name="step.icon" aria-hidden="true" class="size-5" />
            <span class="text-xs tracking-[0.2em]">{{ index + 1 }}</span>
          </span>
        </div>
      </div>

      <article
        :class="cardClasses[step.tone]"
        class="relative min-h-44 overflow-hidden rounded-lg border border-slate-200 bg-slate-50/80 p-5 shadow-xl ring-1 shadow-slate-950/10 ring-slate-200/70 backdrop-blur-sm transition duration-300 group-hover:-translate-y-1 group-hover:bg-slate-100/90 dark:border-grey-100/10 dark:bg-slate-900/45 dark:shadow-slate-950/20 dark:ring-grey-100/5 dark:group-hover:bg-slate-900/70"
      >
        <div aria-hidden="true" class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-slate-300 to-transparent dark:via-grey-100/30"></div>
        <p :class="labelClasses[step.tone]" class="text-[0.68rem] font-bold tracking-[0.24em] uppercase">Step {{ index + 1 }}</p>
        <h3 class="mt-3 text-lg font-semibold text-slate-950 dark:text-grey-50">{{ step.title }}</h3>
        <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-grey-400">{{ step.description }}</p>
      </article>
    </Motion>
  </ol>
</template>
