<script lang="ts" setup>
import { cn } from "@/lib/utils";
import { computed } from "vue";
import type { AdvancedChartPoint, AdvancedChartProps } from "./types";
import { useAdvancedChart } from "./useAdvancedChart";

const props = withDefaults(defineProps<AdvancedChartProps>(), {
  color: "var(--ui-primary)",
  height: 280,
});
const points = computed(() => [...props.data]);
const x = (point: AdvancedChartPoint): number => point.x;
const y = (point: AdvancedChartPoint): number => point.y;
const { components, duration, loadError, mounted } = useAdvancedChart();
</script>

<template>
  <div :class="cn('grid gap-3', props.class)">
    <UAlert v-if="loadError" color="error" description="The chart could not be displayed." title="Chart unavailable" />
    <div v-else-if="points.length === 0" class="rounded-xl border border-content-border p-6 text-sm text-content-subtle">No chart data is available.</div>
    <USkeleton v-else-if="!mounted" :style="{ height: `${height}px` }" class="w-full rounded-xl" />
    <Suspense v-else>
      <component :is="components.XYContainer" :aria-label="ariaLabel" :data="points" :duration="duration" :height="height">
        <component :is="components.Line" :color="color" :duration="duration" :x="x" :y="y" />
      </component>
      <template #fallback><USkeleton :style="{ height: `${height}px` }" class="w-full rounded-xl" /></template>
    </Suspense>
    <details v-if="points.length" class="text-sm text-content-subtle">
      <summary class="cursor-pointer font-medium text-content-body">View chart data</summary>
      <table class="mt-2 w-full text-left">
        <thead>
          <tr>
            <th>Label</th>
            <th>Value</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="point in points" :key="`${point.x}-${point.label}`">
            <td>{{ point.label ?? point.x }}</td>
            <td>{{ point.y }}</td>
          </tr>
        </tbody>
      </table>
    </details>
  </div>
</template>
