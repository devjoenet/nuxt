<script setup lang="ts">
  interface Props {
    kicker: string;
    title: string;
    description?: string;
  }

  defineProps<Props>();
</script>

<template>
  <div class="mx-auto max-w-3xl text-center">
    <p class="text-xs font-bold tracking-[0.22em] text-brand-primary-400 uppercase">{{ kicker }}</p>
    <h2 class="mt-3 text-3xl leading-tight font-semibold text-neutral-50 sm:text-4xl">{{ title }}</h2>
    <p v-if="description" class="mt-4 text-base leading-7 text-neutral-300">{{ description }}</p>
  </div>
</template>
