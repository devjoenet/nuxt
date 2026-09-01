import { usePreferredReducedMotion } from "@vueuse/core";
import { computed, defineAsyncComponent, onMounted, ref, type Component } from "vue";

export function useAdvancedChart() {
  const mounted = ref(false);
  const loadError = ref<string | null>(null);
  const reducedMotion = usePreferredReducedMotion();
  const duration = computed(() => (reducedMotion.value === "reduce" ? 0 : 300));

  const asyncComponent = (loader: () => Promise<{ default: Component }>) =>
    defineAsyncComponent({
      loader: () => loader().then((module) => module.default),
      onError(error, _retry, fail) {
        loadError.value = error instanceof Error ? error.message : "The chart adapter could not load.";
        fail();
      },
    });

  const components = {
    Line: asyncComponent(() => import("@unovis/vue/components/line")),
    XYContainer: asyncComponent(() => import("@unovis/vue/containers/xy-container")),
  };

  onMounted(() => {
    mounted.value = true;
  });

  return { components, duration, loadError, mounted };
}
