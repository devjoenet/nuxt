<script lang="ts" setup>
import { cn } from "@/lib/utils";
import type { AdvancedMapProps } from "./types";
import { useAdvancedMap } from "./useAdvancedMap";

const props = withDefaults(defineProps<AdvancedMapProps>(), {
  center: () => [-84.388, 33.749],
  markers: () => [],
  zoom: 10,
});
const emit = defineEmits<{ markerSelect: [id: string]; ready: [] }>();
const { container, error, loading } = useAdvancedMap(
  props,
  () => emit("ready"),
  (id) => emit("markerSelect", id),
);
</script>

<template>
  <div :class="cn('grid gap-3', props.class)">
    <div class="relative min-h-72 overflow-hidden rounded-xl border border-content-border bg-surface-subtle">
      <div ref="container" :aria-busy="loading" :aria-label="ariaLabel" class="absolute inset-0" role="application" />
      <div v-if="loading" aria-live="polite" class="absolute inset-0 grid place-items-center bg-surface-translucent"><USkeleton class="size-full" /></div>
    </div>
    <UAlert v-if="error" color="error" description="Use the marker list below while the interactive map is unavailable." title="Map unavailable" />
    <details v-if="markers.length" class="text-sm text-content-subtle">
      <summary class="cursor-pointer font-medium text-content-body">Map locations</summary>
      <ul class="mt-2 grid gap-1">
        <li v-for="marker in markers" :key="marker.id">
          <button class="rounded-sm text-primary hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary" type="button" @click="emit('markerSelect', marker.id)">{{ marker.label }}</button>
        </li>
      </ul>
    </details>
  </div>
</template>
