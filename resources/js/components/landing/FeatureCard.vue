<script setup lang="ts">
  import { computed, type Component } from "vue";

  interface LandingCard {
    title: string;
    description: string;
    detail: string;
    icon: Component;
    tone: "primary" | "accent" | "secondary" | "warning";
  }

  interface Props {
    item: LandingCard;
    proof?: string;
    centered?: boolean;
  }

  const props = defineProps<Props>();

  const toneClasses = computed(() => {
    const tones = {
      primary: "text-brand-primary-300 border-brand-primary-400/25 bg-brand-primary-500/5",
      accent: "text-brand-accent-300 border-brand-accent-400/25 bg-brand-accent-500/5",
      secondary: "text-brand-secondary-300 border-brand-secondary-400/25 bg-brand-secondary-500/5",
      warning: "text-brand-warning-300 border-brand-warning-400/25 bg-brand-warning-500/5",
    };

    return tones[props.item.tone];
  });
</script>

<template>
  <article
    class="group h-full rounded-lg border border-brand-primary-400/15 bg-muted-900/70 p-6 shadow-xl shadow-muted-950/20 ring-1 ring-neutral-100/5 transition hover:-translate-y-1 hover:border-brand-primary-300/35 hover:bg-muted-900"
    :class="{ 'text-center': centered }"
  >
    <div class="flex" :class="centered ? 'justify-center' : 'justify-start'">
      <div class="flex size-12 items-center justify-center rounded-md border" :class="toneClasses">
        <component :is="item.icon" class="size-6" aria-hidden="true" />
      </div>
    </div>

    <h3 class="mt-5 text-lg font-semibold text-neutral-50">{{ item.title }}</h3>
    <p class="mt-3 text-sm leading-6 text-neutral-300">{{ item.description }}</p>
    <p class="mt-1 text-sm leading-6 text-neutral-400">{{ item.detail }}</p>
    <p v-if="proof" class="mt-5 border-t border-neutral-100/10 pt-4 text-xs leading-5 text-neutral-400">{{ proof }}</p>
  </article>
</template>
