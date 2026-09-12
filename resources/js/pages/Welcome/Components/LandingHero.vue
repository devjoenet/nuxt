<script setup lang="ts">
import DashboardPreview from "@/pages/Welcome/Components/Previews/DashboardPreview.vue";
import LandingHeroPicture from "@/pages/Welcome/Components/LandingHeroPicture.vue";
import type { HeroAssetSet } from "@/composables/useLandingHeroAssets";
import type { Appearance } from "@/types";
import { Motion } from "motion-v";

defineProps<{
  appearance: Appearance;
  darkHero: HeroAssetSet;
  lightHero: HeroAssetSet;
}>();
</script>

<template>
  <section id="hero" class="relative min-h-screen scroll-mt-28 overflow-hidden px-5 pt-32 pb-16 sm:px-8 lg:pt-36">
    <LandingHeroPicture :appearance="appearance" :dark-hero="darkHero" fetch-priority="high" image-class="absolute inset-0 size-full object-cover object-center" :light-hero="lightHero" picture-class="absolute inset-0 block size-full" />
    <div
      aria-hidden="true"
      class="absolute inset-0 bg-[linear-gradient(90deg,rgba(1,11,16,0.86)_0%,rgba(1,11,16,0.54)_38%,rgba(1,11,16,0.14)_72%,rgba(1,11,16,0.3)_100%)] dark:bg-[linear-gradient(90deg,rgba(1,11,16,0.94)_0%,rgba(1,11,16,0.66)_38%,rgba(1,11,16,0.22)_72%,rgba(1,11,16,0.42)_100%)]"
    ></div>
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_76%_28%,rgba(110,104,182,0.26),transparent_22rem),radial-gradient(circle_at_22%_70%,rgba(208,57,120,0.18),transparent_24rem),linear-gradient(180deg,rgba(1,11,16,0.08),rgba(1,11,16,0.88))]" aria-hidden="true"></div>

    <div class="relative mx-auto grid max-w-7xl gap-10 lg:min-h-[calc(100vh-9rem)] lg:grid-cols-[0.78fr_1.22fr] lg:items-center">
      <Motion :animate="{ opacity: 1, x: 0 }" :initial="{ opacity: 0, x: -34 }" :transition="{ duration: 0.8, ease: 'easeOut' }" as="div" class="max-w-2xl">
        <p class="mb-5 text-sm font-bold tracking-[0.24em] text-secondary-400 uppercase">Custom operations software</p>
        <h1 class="max-w-3xl text-4xl leading-[1.02] font-black text-grey-50 uppercase sm:text-6xl lg:text-7xl">
          Software that fits
          <span class="block text-primary-400">how work moves.</span>
        </h1>
        <p class="mt-6 max-w-lg text-base leading-7 text-grey-100 dark:text-grey-200">We turn fragmented operations into secure web applications for the teams moving data, approvals, inventory, and decisions every day.</p>

        <div class="mt-8 flex flex-col gap-3 sm:flex-row">
          <a
            href="#contact"
            class="inline-flex items-center justify-center rounded-md bg-primary-500 px-6 py-3 text-sm font-bold text-primary-950 shadow-lg shadow-primary-500/25 transition hover:bg-primary-400 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-100"
          >
            Start a workflow audit
            <UIcon aria-hidden="true" class="ml-2 size-4" name="i-ph-arrow-circle-right" />
          </a>
          <a
            href="#services"
            class="inline-flex items-center justify-center rounded-md border border-secondary-400/80 px-6 py-3 text-sm font-semibold text-secondary-50 transition hover:border-secondary-300 hover:bg-secondary-500/15 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-secondary-400 dark:border-secondary-500/80 dark:text-secondary-100 dark:hover:border-secondary-400"
          >
            See what we build
          </a>
        </div>
      </Motion>

      <Motion :animate="{ opacity: 1, x: 0, y: 0 }" :initial="{ opacity: 0, x: 34, y: 18 }" :transition="{ duration: 0.9, delay: 0.15, ease: 'easeOut' }" as="div">
        <DashboardPreview />
      </Motion>
    </div>
  </section>
</template>
