<script setup lang="ts">
import { PhVideoCamera, PhCloudSun } from "@phosphor-icons/vue";
import { LazyAdvancedChart } from "@/components/AdvancedChart";
import { Badge } from "@/components/Ui/Badge";
import { Card } from "@/components/Ui/Card";
import { ref } from "vue";
import ReferenceMetrics from "./ReferenceMetrics.vue";
const camera = ref("Lounge");
const recording = ref(true);
const temperature = ref(21);
const devices = ref([
  { name: "Humidity", active: false },
  { name: "Temperature", active: true },
  { name: "Air Conditioner", active: false },
  { name: "Lights", active: false },
  { name: "Wi-fi", active: true },
]);
</script>
<template>
  <div class="grid gap-5">
    <div class="grid gap-5 lg:grid-cols-[minmax(0,2fr)_minmax(0,1fr)]">
      <Card variant="outline"
        ><div class="mb-4 flex flex-wrap items-center justify-between gap-3">
          <h3 class="text-lg font-semibold text-content-heading">Cameras</h3>
          <div class="flex gap-2"><UButton v-for="room in ['Lounge', 'Office', 'Attic']" :key="room" :label="room" :variant="camera === room ? 'solid' : 'outline'" :aria-pressed="camera === room" @click="camera = room" /></div>
        </div>
        <div class="grid min-h-64 place-content-center gap-4 rounded-xl bg-surface-subtle p-8 text-center">
          <PhVideoCamera aria-hidden="true" class="mx-auto size-16 text-primary" />
          <p class="text-2xl font-semibold text-content-heading">{{ camera }} camera</p>
          <p class="text-sm text-content-subtle">Demo camera preview</p>
          <Badge :label="recording ? 'Recording' : 'Paused'" :tone="recording ? 'success' : 'neutral'" variant="outline" class="mx-auto" />
        </div>
        <UButton class="mt-4" :label="recording ? 'Pause camera' : 'Resume camera'" variant="outline" @click="recording = !recording" /></Card
      ><Card variant="soft" tone="primary" class="grid content-center gap-5 text-center"
        ><PhCloudSun aria-hidden="true" class="mx-auto size-20 text-primary" />
        <h3 class="text-lg font-semibold text-content-heading">Weather today</h3>
        <p class="text-3xl font-semibold text-content-heading">San Francisco · 29°C</p>
        <p class="text-content-body">Cloudy</p></Card
      >
    </div>
    <ReferenceMetrics
      :items="[
        { label: 'Living Room Temperature', value: `${temperature} °C` },
        { label: 'Outside Humidity', value: '44%' },
        { label: 'Water Consumption', value: '87 m³' },
        { label: 'Internet', value: '417 GB' },
      ]"
    />
    <div class="grid gap-5 lg:grid-cols-3">
      <Card variant="outline"
        ><h3 class="mb-4 font-semibold text-content-heading">Consumption by room</h3>
        <p class="mb-4 text-3xl font-semibold text-content-heading">310.0 W</p>
        <div v-for="(room, i) in ['Living Room', 'Kitchen', 'Attic', 'Garage', 'Basement']" :key="room" class="mb-3">
          <p class="mb-1 text-sm text-content-body">{{ room }} · {{ [15, 20, 13, 32, 20][i] }}%</p>
          <UProgress :model-value="[15, 20, 13, 32, 20][i]" :aria-label="room" /></div></Card
      ><Card class="min-w-0" variant="outline"
        ><h3 class="font-semibold text-content-heading">Consumption per day</h3>
        <LazyAdvancedChart
          v-bind="{ ariaLabel: 'Daily energy consumption' }"
          :data="[
            { x: 1, y: 120 },
            { x: 2, y: 230 },
            { x: 3, y: 160 },
            { x: 4, y: 310 },
            { x: 5, y: 270 },
          ]" /></Card
      ><Card variant="outline"
        ><h3 class="font-semibold text-content-heading">Device limit</h3>
        <p class="my-8 text-center text-5xl font-semibold text-content-heading">{{ temperature }}°C</p>
        <label for="demo-temperature" class="block text-sm text-content-body">Temperature</label><input id="demo-temperature" v-model.number="temperature" class="mt-4 w-full accent-primary" type="range" min="16" max="38" />
        <p class="mt-3 flex justify-between text-sm text-content-subtle"><span>16°C</span><span>38°C</span></p></Card
      >
    </div>
    <section aria-label="Home devices" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
      <Card v-for="device in devices" :key="device.name" variant="outline"
        ><USwitch v-model="device.active" :label="device.name" />
        <p class="mt-4 text-sm text-content-subtle">{{ device.active ? "Active" : "Off" }}</p></Card
      >
    </section>
  </div>
</template>
