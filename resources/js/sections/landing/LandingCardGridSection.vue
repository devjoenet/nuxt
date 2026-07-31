<script setup lang="ts">
import FeatureCard from "@/components/landing/FeatureCard.vue";
import SectionHeading from "@/components/landing/SectionHeading.vue";
import { cn } from "@/lib/utils";
import type { LandingCard, ServiceItem } from "@/sections/landing/landingContent";

const props = withDefaults(
  defineProps<{
    id: string;
    kicker: string;
    title: string;
    tone: "primary" | "secondary" | "accent";
    items: Array<LandingCard | ServiceItem>;
    ordered?: boolean;
    centered?: boolean;
    gridClass?: string;
    class?: string;
  }>(),
  {
    ordered: false,
    centered: false,
    gridClass: "md:grid-cols-3",
  },
);
</script>

<template>
  <section :id="id" :class="cn('scroll-mt-28 px-5 py-16 sm:px-8 lg:py-20', props.class)">
    <div class="mx-auto max-w-7xl">
      <SectionHeading :animated="false" :kicker="kicker" :title="title" :tone="tone" />

      <component :is="ordered ? 'ol' : 'ul'" :class="cn('mt-8 grid gap-5', gridClass)">
        <li v-for="item in items" :key="item.title" class="relative">
          <FeatureCard :centered="centered" :item="item" :proof="'proof' in item ? item.proof : undefined" />
        </li>
      </component>
    </div>
  </section>
</template>
