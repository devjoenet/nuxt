<script setup lang="ts">
import { Motion } from "motion-v";
import LandingContactForm from "@/pages/Welcome/Components/LandingContactForm.vue";
import { cn } from "@/lib/utils";
import type { Option } from "@/types/crm";

const props = defineProps<{
  serviceInterestOptions: Option[];
  class?: string;
}>();
</script>

<template>
  <section
    id="contact"
    :class="
      cn(
        'scroll-mt-28 border-y border-argon-accent-500/15 bg-[radial-gradient(circle_at_center,rgba(208,57,120,0.13),transparent_38rem),radial-gradient(circle_at_78%_50%,rgba(110,104,182,0.13),transparent_26rem)] px-5 py-16 sm:px-8 lg:py-20 dark:bg-[radial-gradient(circle_at_center,rgba(208,57,120,0.16),transparent_38rem),radial-gradient(circle_at_78%_50%,rgba(110,104,182,0.16),transparent_26rem)]',
        props.class,
      )
    "
  >
    <Motion
      as="div"
      :initial="{ opacity: 0, y: 30, scale: 0.98 }"
      :transition="{ duration: 0.75, delay: 0.1, ease: 'easeOut' }"
      :viewport="{ once: true, amount: 0.22 }"
      :while-in-view="{ opacity: 1, y: 0, scale: 1 }"
      class="mx-auto grid max-w-6xl gap-10 rounded-xl border border-argon-accent-500/25 bg-slate-50/90 p-6 shadow-2xl ring-1 shadow-argon-accent-950/10 ring-slate-200/70 sm:p-8 lg:grid-cols-[0.72fr_1.28fr] lg:p-10 dark:bg-slate-900/85 dark:shadow-argon-accent-950/30 dark:ring-transparent"
    >
      <div>
        <p class="text-sm font-semibold text-secondary-600 dark:text-secondary-300">Ready to replace the workaround?</p>
        <h2 class="mt-3 text-3xl leading-tight font-semibold text-slate-950 dark:text-grey-50">Let’s map the workflow before anyone writes code.</h2>
        <p class="mt-4 leading-7 text-slate-700 dark:text-grey-300">Tell us where the process breaks down, what your team is doing manually, and what a useful result looks like. Specifics beat polished sales language every time.</p>
        <ul class="mt-6 space-y-3 text-sm text-slate-700 dark:text-grey-300" role="list">
          <li class="flex gap-3"><UIcon class="mt-0.5 size-5 shrink-0 text-primary-600 dark:text-primary-300" name="i-ph-check-circle" />No generic package pitch</li>
          <li class="flex gap-3"><UIcon class="mt-0.5 size-5 shrink-0 text-primary-600 dark:text-primary-300" name="i-ph-check-circle" />Practical technical review</li>
          <li class="flex gap-3"><UIcon class="mt-0.5 size-5 shrink-0 text-primary-600 dark:text-primary-300" name="i-ph-check-circle" />Clear next step, even if it is “not yet”</li>
        </ul>
      </div>
      <LandingContactForm :service-interest-options="serviceInterestOptions" />
    </Motion>
  </section>
</template>
