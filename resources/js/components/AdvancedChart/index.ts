import { defineAsyncComponent } from "vue";

export { default as AdvancedChart } from "./AdvancedChart.vue";
export const LazyAdvancedChart = defineAsyncComponent(() => import("./AdvancedChart.vue"));
export type { AdvancedChartPoint, AdvancedChartProps } from "./types";
