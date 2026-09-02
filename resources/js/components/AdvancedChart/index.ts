import { defineAsyncComponent } from "vue";

export { default as AdvancedChart } from "./AdvancedChart.vue";
export const LazyAdvancedChart = defineAsyncComponent(() => import("./lazy"));
export type { AdvancedChartPoint, AdvancedChartProps } from "./types";
