<script setup lang="ts">
  import { Motion } from "motion-v";
  import OperationsShowcase from "@/components/landing/OperationsShowcase.vue";
  import { cn } from "@/lib/utils";

  const props = defineProps<{
    heroImageSrc: string;
    class?: string;
  }>();
</script>

<template>
  <section id="operations" :class="cn('relative scroll-mt-28 border-y border-secondary-500/15 px-5 py-16 sm:px-8 lg:py-20', props.class)">
    <div
      aria-hidden="true"
      class="absolute inset-0 bg-[radial-gradient(circle_at_18%_10%,rgba(110,104,182,0.18),transparent_26rem),radial-gradient(circle_at_82%_55%,rgba(208,57,120,0.14),transparent_24rem)] dark:bg-[radial-gradient(circle_at_18%_10%,rgba(110,104,182,0.16),transparent_26rem),radial-gradient(circle_at_82%_55%,rgba(208,57,120,0.12),transparent_24rem)]"
    />

    <div class="relative mx-auto grid max-w-7xl gap-10 lg:grid-cols-[0.65fr_1.35fr] lg:items-center">
      <Motion
        as="div"
        :initial="{ opacity: 0, y: 24 }"
        :transition="{ duration: 0.7, ease: 'easeOut' }"
        :viewport="{ once: true, amount: 0.3 }"
        :while-in-view="{ opacity: 1, y: 0 }"
      >
        <p class="text-xs font-bold tracking-[0.22em] text-secondary-400 uppercase">Built for operations</p>
        <h2 class="mt-4 max-w-lg text-3xl leading-tight font-semibold text-muted-950 sm:text-4xl dark:text-neutral-50">Interfaces your team can actually use.</h2>
        <p class="mt-4 max-w-md text-base leading-7 text-muted-700 dark:text-neutral-300">Clean, practical, and built for the way operations move.</p>
        <a
          class="mt-8 inline-flex items-center justify-center rounded-md border border-secondary-500/80 px-5 py-3 text-sm font-semibold text-secondary-800 transition hover:border-secondary-500 hover:bg-secondary-500/10 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-secondary-400 dark:text-secondary-100 dark:hover:border-secondary-400 dark:hover:bg-secondary-500/15"
          href="#services"
        >
          See more examples
          <UIcon aria-hidden="true" class="ml-2 size-4" name="i-ph-arrow-circle-right" />
        </a>
      </Motion>

      <OperationsShowcase :hero-image-src="heroImageSrc" />
    </div>
  </section>
</template>
