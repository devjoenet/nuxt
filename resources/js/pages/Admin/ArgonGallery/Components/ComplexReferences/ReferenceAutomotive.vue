<script setup lang="ts">
import { PhCarProfile } from "@phosphor-icons/vue";
import { LazyAdvancedMap } from "@/components/AdvancedMap";
import { Card } from "@/components/Ui/Card";
import { ref } from "vue";
import ReferenceMetrics from "./ReferenceMetrics.vue";
const volume = ref(15);
const playing = ref(false);
const selected = ref("");
const markers = [
  { id: "charger", label: "Miclan charging station · 891 Limarenda road", latitude: 33.755, longitude: -84.39 },
  { id: "destination", label: "Creative Tim · destination", latitude: 33.74, longitude: -84.38 },
];
</script>
<template>
  <div class="grid gap-5">
    <Card variant="soft" tone="primary"
      ><div class="grid items-center gap-8 md:grid-cols-2">
        <div>
          <h3 class="text-xl font-semibold text-content-heading">Since Last Charge</h3>
          <dl class="mt-5 grid grid-cols-3 gap-4 text-content-body">
            <div>
              <dt class="text-sm">Distance</dt>
              <dd class="text-2xl font-semibold">145 Km</dd>
            </div>
            <div>
              <dt class="text-sm">Average Energy</dt>
              <dd class="text-2xl font-semibold">300 Kw</dd>
            </div>
            <div>
              <dt class="text-sm">Available Range</dt>
              <dd class="text-2xl font-semibold">47%</dd>
            </div>
          </dl>
        </div>
        <div class="grid place-items-center rounded-xl bg-surface-elevated p-8">
          <PhCarProfile aria-hidden="true" class="size-32 text-primary" />
          <p class="text-sm text-content-subtle">Electric vehicle · parked</p>
        </div>
      </div></Card
    >
    <ReferenceMetrics
      :items="[
        { label: `Today's Trip`, value: '145 Km' },
        { label: 'Battery Health', value: '99%' },
        { label: 'Average Speed', value: '56 Km/h' },
        { label: 'Music Volume', value: `${volume}/100` },
      ]"
    />
    <div class="grid gap-5 xl:grid-cols-[minmax(0,2fr)_minmax(0,1fr)]">
      <Card class="min-w-0" variant="outline"
        ><h3 class="mb-4 text-lg font-semibold text-content-heading">Nearest Charger</h3>
        <LazyAdvancedMap v-bind="{ ariaLabel: 'Trip and charging locations' }" :markers="markers" @marker-select="selected = markers.find((marker) => marker.id === $event)?.label ?? ''" />
        <p v-if="selected" role="status" class="mt-3 text-content-body">{{ selected }}</p></Card
      >
      <div class="grid gap-5">
        <Card variant="outline"
          ><h3 class="font-semibold text-content-heading">Estimated arrival time</h3>
          <p class="mt-4 text-3xl font-semibold text-content-heading">11:13</p>
          <p class="mt-3 text-content-body">Turn right in 2.4 miles</p>
          <p class="mt-2 text-content-subtle">6.3 Km to Creative Tim</p></Card
        ><Card variant="outline"
          ><h3 class="font-semibold text-content-heading">You're Mines Still (feat Drake)</h3>
          <p class="mb-5 text-content-subtle">Yung Bleu · Hip-Hop</p>
          <UButton :label="playing ? 'Pause demo playback' : 'Play demo playback'" :icon="playing ? 'i-ph-pause' : 'i-ph-play'" :aria-pressed="playing" @click="playing = !playing" /><label for="demo-volume" class="mt-5 block text-sm text-content-body">Volume {{ volume }}%</label
          ><input id="demo-volume" v-model.number="volume" class="mt-2 w-full accent-primary" type="range" min="0" max="100" />
          <p role="status" class="mt-2 text-sm text-content-subtle">{{ playing ? "Demo playback active · no audio stream" : "Playback paused" }}</p></Card
        >
      </div>
    </div>
  </div>
</template>
