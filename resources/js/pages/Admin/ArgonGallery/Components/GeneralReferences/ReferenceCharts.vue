<script setup lang="ts">
import { LazyAdvancedChart } from "@/components/AdvancedChart";
import { Card } from "@/components/Ui/Card";

const revenue = [
  { x: 1, y: 48, label: "April" },
  { x: 2, y: 62, label: "May" },
  { x: 3, y: 51, label: "June" },
  { x: 4, y: 86, label: "July" },
  { x: 5, y: 74, label: "August" },
  { x: 6, y: 105, label: "September" },
];
const audience = revenue.map((point, index) => ({ ...point, y: [32, 44, 70, 59, 91, 118][index] ?? point.y }));
const bars = [42, 68, 54, 92, 73, 58];
</script>

<template>
  <div class="grid gap-5">
    <p class="max-w-[56ch] text-base text-pretty text-content-body sm:text-sm">The source chart matrix is adapted through the approved lazy chart boundary, with readable data available alongside each visual.</p>
    <div class="grid gap-5 lg:grid-cols-2">
      <Card variant="outline">
        <template #header><h3 class="text-lg font-semibold text-balance text-content-heading">Revenue trend</h3></template>
        <LazyAdvancedChart v-bind="{ ariaLabel: 'Monthly revenue trend' }" :data="revenue" :height="240" />
      </Card>
      <Card variant="outline">
        <template #header><h3 class="text-lg font-semibold text-balance text-content-heading">Audience growth</h3></template>
        <LazyAdvancedChart v-bind="{ ariaLabel: 'Monthly audience growth' }" color="var(--color-success)" :data="audience" :height="240" />
      </Card>
      <Card class="grid gap-5" variant="outline">
        <template #header><h3 class="text-lg font-semibold text-balance text-content-heading">Quarterly comparison</h3></template>
        <div class="flex h-56 items-end gap-3" role="img" aria-label="Bar chart with values 42, 68, 54, 92, 73, and 58">
          <div v-for="(bar, index) in bars" :key="bar + index" class="grid h-full flex-1 items-end gap-2">
            <div class="rounded-t-md bg-primary" :style="{ height: `${bar}%` }" />
            <p class="text-center text-base text-content-subtle sm:text-sm">Q{{ index + 1 }}</p>
          </div>
        </div>
      </Card>
      <Card class="grid place-items-center gap-5" variant="outline">
        <template #header><h3 class="text-lg font-semibold text-balance text-content-heading">Channel mix</h3></template>
        <div
          class="grid size-52 place-items-center rounded-full [background:conic-gradient(var(--color-primary)_0_44%,var(--color-success)_44%_72%,var(--color-warning)_72%_89%,var(--color-info)_89%)]"
          role="img"
          aria-label="Channel mix: direct 44 percent, search 28 percent, referrals 17 percent, social 11 percent"
        >
          <div class="grid size-28 place-items-center rounded-full bg-surface-elevated text-center"><strong class="text-2xl font-semibold text-content-heading tabular-nums">100%</strong></div>
        </div>
      </Card>
    </div>
  </div>
</template>
