import type { Map as MapLibreMap, StyleSpecification } from "maplibre-gl";
import { onBeforeUnmount, onMounted, ref, useTemplateRef } from "vue";
import type { AdvancedMapProps } from "./types";

const emptyStyle: StyleSpecification = { version: 8, sources: {}, layers: [] };

export function useAdvancedMap(props: AdvancedMapProps, onReady: () => void, onMarkerSelect: (id: string) => void) {
  const container = useTemplateRef<HTMLElement>("container");
  const error = ref<string | null>(null);
  const loading = ref(true);
  let disposed = false;
  let map: MapLibreMap | null = null;

  onMounted(async () => {
    try {
      const maplibre = await import("maplibre-gl");
      const { default: workerUrl } = await import("maplibre-gl/dist/maplibre-gl-worker.mjs?worker&url");
      await import("maplibre-gl/dist/maplibre-gl.css");

      if (disposed || !container.value) return;

      maplibre.setWorkerUrl(workerUrl);
      map = new maplibre.Map({ container: container.value, center: props.center, zoom: props.zoom, style: props.styleUrl ?? emptyStyle, attributionControl: false });
      map.addControl(new maplibre.NavigationControl(), "top-right");
      props.markers?.forEach((marker) => {
        const element = document.createElement("button");
        element.type = "button";
        element.className = "size-4 rounded-full border-2 border-white bg-primary shadow-md focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary";
        element.ariaLabel = marker.label;
        element.addEventListener("click", () => onMarkerSelect(marker.id));
        new maplibre.Marker({ element }).setLngLat([marker.longitude, marker.latitude]).addTo(map as MapLibreMap);
      });
      map.once("load", () => {
        loading.value = false;
        onReady();
      });
    } catch (reason: unknown) {
      loading.value = false;
      error.value = reason instanceof Error ? reason.message : "The map adapter could not load.";
    }
  });

  onBeforeUnmount(() => {
    disposed = true;
    map?.remove();
    map = null;
  });

  return { container, error, loading };
}
