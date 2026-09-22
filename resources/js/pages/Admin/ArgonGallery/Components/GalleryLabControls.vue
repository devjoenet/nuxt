<script setup lang="ts">
import type { Appearance } from "@/types";
import type { ArgonGalleryLabId, ArgonGalleryViewport } from "@/types/argonGallery";
import { galleryLabs } from "./galleryLabs";

defineProps<{ appearance: Appearance; lab: ArgonGalleryLabId; viewport: ArgonGalleryViewport }>();
const emit = defineEmits<{ appearance: [value: Appearance]; lab: [value: ArgonGalleryLabId]; viewport: [value: ArgonGalleryViewport] }>();
const appearances: { label: string; value: Appearance }[] = [
  { label: "System", value: "system" },
  { label: "Light", value: "light" },
  { label: "Dark", value: "dark" },
];
const viewports: { label: string; value: ArgonGalleryViewport }[] = [
  { label: "Full", value: "full" },
  { label: "Mobile", value: "mobile" },
  { label: "Tablet", value: "tablet" },
  { label: "Desktop", value: "desktop" },
];
</script>

<template>
  <div class="bg-surface-subtle grid gap-4 rounded-xl border border-content-border p-4">
    <div class="grid gap-2">
      <p class="text-base font-medium text-content-heading sm:text-sm">Laboratory</p>
      <div class="flex max-w-full gap-1 overflow-x-auto" role="tablist">
        <UButton v-for="item in galleryLabs" :key="item.id" :aria-selected="lab === item.id" color="neutral" :label="item.label" role="tab" size="sm" :variant="lab === item.id ? 'soft' : 'ghost'" @click="emit('lab', item.id)" />
      </div>
    </div>
    <div class="grid gap-4 sm:grid-cols-2">
      <fieldset class="grid gap-2">
        <legend class="text-base font-medium text-content-heading sm:text-sm">Theme</legend>
        <div class="flex flex-wrap gap-1">
          <UButton
            v-for="item in appearances"
            :key="item.value"
            :aria-pressed="appearance === item.value"
            color="neutral"
            :data-testid="`gallery-theme-${item.value}`"
            :label="item.label"
            size="sm"
            :variant="appearance === item.value ? 'soft' : 'ghost'"
            @click="emit('appearance', item.value)"
          />
        </div>
      </fieldset>
      <fieldset class="grid gap-2">
        <legend class="text-base font-medium text-content-heading sm:text-sm">Representative viewport</legend>
        <div class="flex flex-wrap gap-1">
          <UButton
            v-for="item in viewports"
            :key="item.value"
            :aria-pressed="viewport === item.value"
            color="neutral"
            :data-testid="`gallery-viewport-${item.value}`"
            :label="item.label"
            size="sm"
            :variant="viewport === item.value ? 'soft' : 'ghost'"
            @click="emit('viewport', item.value)"
          />
        </div>
      </fieldset>
    </div>
  </div>
</template>
