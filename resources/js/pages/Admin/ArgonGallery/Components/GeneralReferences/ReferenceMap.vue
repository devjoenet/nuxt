<script setup lang="ts">
import { LazyAdvancedMap } from "@/components/AdvancedMap";
import { Card } from "@/components/Ui/Card";
import { computed, ref } from "vue";

const props = defineProps<{ specimen: "google" | "vector" }>();
const selectedMarker = ref<string | null>(null);
const markers = computed(() =>
  props.specimen === "google"
    ? [
        { id: "atlanta", label: "Atlanta studio", latitude: 33.749, longitude: -84.388 },
        { id: "decatur", label: "Decatur workspace", latitude: 33.7748, longitude: -84.2963 },
      ]
    : [
        { id: "new-york", label: "New York", latitude: 40.7128, longitude: -74.006 },
        { id: "london", label: "London", latitude: 51.5072, longitude: -0.1276 },
        { id: "tokyo", label: "Tokyo", latitude: 35.6762, longitude: 139.6503 },
      ],
);
const center = computed<[number, number]>(() => (props.specimen === "google" ? [-84.35, 33.76] : [16, 30]));
</script>

<template>
  <div class="grid gap-5">
    <UAlert
      color="info"
      :description="specimen === 'google' ? 'The redirect-only Google example is adapted to the approved MapLibre boundary, avoiding a second map runtime.' : 'The redirect-only vector example uses the same typed map boundary with a wider geographic fixture.'"
      title="Map runtime divergence"
      variant="subtle"
    />
    <Card as="section" class="grid gap-4" variant="outline">
      <LazyAdvancedMap v-bind="{ ariaLabel: `${specimen} map reference` }" :center="center" :markers="markers" :zoom="specimen === 'google' ? 10 : 1" @marker-select="selectedMarker = $event" />
      <p aria-live="polite" class="text-base text-content-body sm:text-sm">{{ selectedMarker ? `Selected marker: ${selectedMarker}` : "Select a map marker to inspect its inert state." }}</p>
    </Card>
  </div>
</template>
