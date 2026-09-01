import { defineAsyncComponent } from "vue";

export { default as AdvancedEditor } from "./AdvancedEditor.vue";
export const LazyAdvancedEditor = defineAsyncComponent(() => import("./lazy"));
