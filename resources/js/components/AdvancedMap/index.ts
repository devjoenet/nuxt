import { defineAsyncComponent } from "vue";

export { default as AdvancedMap } from "./AdvancedMap.vue";
export const LazyAdvancedMap = defineAsyncComponent(() => import("./lazy"));
export type { AdvancedMapMarker, AdvancedMapProps } from "./types";
