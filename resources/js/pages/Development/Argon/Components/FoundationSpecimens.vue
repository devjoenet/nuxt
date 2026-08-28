<script lang="ts" setup>
const colorSamples = [
  { label: "primary", class: "bg-primary" },
  { label: "secondary", class: "bg-secondary" },
  { label: "success", class: "bg-success" },
  { label: "info", class: "bg-info" },
  { label: "warning", class: "bg-warning" },
  { label: "error", class: "bg-error" },
];
</script>

<template>
  <section aria-labelledby="foundation-specimens" class="grid gap-6">
    <header>
      <p class="text-xs font-bold tracking-wider text-primary uppercase">Argon foundations</p>
      <h2 id="foundation-specimens" class="mt-1 text-2xl font-semibold text-content-heading">Semantic token specimens</h2>
    </header>
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
      <div v-for="sample in colorSamples" :key="sample.label" class="grid gap-2 rounded-lg border border-content-border bg-surface-elevated p-3">
        <span :class="sample.class" class="h-12 rounded-md" /><span class="text-xs font-semibold text-content-heading capitalize">{{ sample.label }}</span>
      </div>
    </div>
    <div class="grid gap-4 md:grid-cols-3">
      <div class="rounded-xl bg-surface-canvas p-5 text-content-heading shadow-xs">Canvas / shadow XS</div>
      <div class="rounded-xl bg-surface-elevated p-5 text-content-heading shadow-md">Elevated / shadow MD</div>
      <div class="rounded-xl bg-surface-translucent p-5 text-content-heading shadow-xl backdrop-blur-argon">Translucent / blur</div>
    </div>
    <div class="grid gap-2">
      <p class="text-4xl font-light tracking-tight text-content-heading">Display typography</p>
      <p class="text-base leading-relaxed text-content-body">Body copy uses the authored Open Sans stack and semantic content color.</p>
      <p class="text-xs font-bold tracking-wider text-content-subtle uppercase">Caption and eyebrow</p>
    </div>
  </section>
</template>
