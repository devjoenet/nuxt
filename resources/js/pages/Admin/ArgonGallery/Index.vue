<script setup lang="ts">
import type { ArgonGalleryExample, ArgonGalleryFamily, ArgonGallerySummary } from "@/types/argonGallery";
import GalleryExplorer from "./Components/GalleryExplorer.vue";
import GalleryLaboratory from "./Components/GalleryLaboratory.vue";
import GalleryStatus from "./Components/GalleryStatus.vue";

defineProps<{
  examples: ArgonGalleryExample[];
  family_counts: Record<ArgonGalleryFamily, number>;
  summary: ArgonGallerySummary;
}>();
</script>

<template>
  <div class="grid gap-10"><GalleryStatus :summary="summary" /><GalleryLaboratory /><GalleryExplorer :examples="examples" :family-counts="family_counts" /></div>
</template>
