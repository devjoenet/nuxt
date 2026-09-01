<script setup lang="ts">
import type { ArgonGalleryExample } from "@/types/argonGallery";
import GalleryExampleDetails from "./Components/GalleryExampleDetails.vue";

defineProps<{ example: ArgonGalleryExample }>();
</script>

<template>
  <GalleryExampleDetails :example="example" />
</template>
