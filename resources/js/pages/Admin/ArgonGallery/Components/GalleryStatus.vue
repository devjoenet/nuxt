<script setup lang="ts">
import type { ArgonGallerySummary } from "@/types/argonGallery";
import { computed } from "vue";

const props = defineProps<{ summary: ArgonGallerySummary }>();
const stats = computed(() => [
  { label: "Manifest entries", value: props.summary.total },
  { label: "Routable entries", value: props.summary.routable },
  { label: "Source available", value: props.summary.available },
  { label: "Empty sources", value: props.summary.empty },
]);
</script>

<template>
  <section aria-labelledby="gallery-status-title" class="grid gap-4">
    <div class="flex flex-wrap items-start justify-between gap-3">
      <div class="min-w-0">
        <h2 id="gallery-status-title" class="text-xl font-semibold text-balance text-content-heading">Manifest status</h2>
        <p class="max-w-[70ch] text-base text-pretty text-content-body sm:text-sm">{{ summary.product }} {{ summary.version }} is represented by protected routes and metadata only. {{ summary.source_publication }}</p>
      </div>
      <UBadge :color="summary.complete ? 'success' : 'warning'" :label="summary.complete ? 'Complete' : 'Incomplete'" variant="subtle" />
    </div>
    <dl class="@container grid gap-y-4 border-y border-content-border py-4 @sm:grid-cols-2 @3xl:grid-cols-4">
      <div v-for="(stat, index) in stats" :key="stat.label" :class="index ? '@sm:border-l @sm:border-content-border @sm:pl-4' : ''">
        <dt class="truncate text-base font-medium text-content-body sm:text-sm">{{ stat.label }}</dt>
        <dd class="text-2xl font-semibold text-content-heading tabular-nums">{{ stat.value }}</dd>
      </div>
    </dl>
  </section>
</template>
