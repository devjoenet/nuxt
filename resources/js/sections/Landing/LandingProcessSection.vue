<script setup lang="ts">
import ProcessTimeline from "@/components/Landing/ProcessTimeline.vue";
import SectionHeading from "@/components/Landing/SectionHeading.vue";
import { cn } from "@/lib/utils";
import type { ProcessStep } from "@/sections/Landing/landingContent";

const props = defineProps<{
  steps: ProcessStep[];
  class?: string;
}>();
</script>

<template>
  <section id="process" :class="cn('scroll-mt-28 px-5 py-16 sm:px-8 lg:py-20', props.class)">
    <div class="mx-auto max-w-7xl">
      <SectionHeading kicker="Our process" title="Map first. Build second." tone="accent" />
      <ProcessTimeline :steps="steps" />
    </div>
  </section>
</template>
