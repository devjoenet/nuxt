<script setup lang="ts">
import { useAppearance } from "@/composables/useAppearance";
import type { ArgonGalleryLabId, ArgonGalleryViewport } from "@/types/argonGallery";
import { computed, ref } from "vue";
import GalleryLabControls from "./GalleryLabControls.vue";
import { galleryLabs } from "./galleryLabs";

const { appearance, setAppearance } = useAppearance();
const activeLab = ref<ArgonGalleryLabId>("foundations");
const viewport = ref<ArgonGalleryViewport>("full");
const definition = computed(() => galleryLabs[activeLab.value]);
const viewportWidths: Record<ArgonGalleryViewport, string> = { full: "100%", mobile: "390px", tablet: "768px", desktop: "1280px" };
const previewStyle = computed(() => ({ "--gallery-viewport": viewportWidths[viewport.value] }));
</script>

<template>
  <section id="component-laboratory" aria-labelledby="component-laboratory-title" class="grid scroll-mt-28 gap-5">
    <header>
      <h2 id="component-laboratory-title" class="text-xl font-semibold text-balance text-content-heading">Component laboratory</h2>
      <p class="max-w-[70ch] text-base text-pretty text-content-body sm:text-sm">Inspect typed states in isolation, switch the application theme, and constrain the preview to representative widths without loading licensed resources.</p>
    </header>
    <GalleryLabControls :appearance="appearance" :lab="activeLab" :viewport="viewport" @appearance="setAppearance" @lab="activeLab = $event" @viewport="viewport = $event" />
    <div class="overflow-x-auto rounded-xl bg-surface-canvas p-3 sm:p-5">
      <div :style="previewStyle" class="mx-auto w-full max-w-(--gallery-viewport) rounded-xl border border-content-border bg-surface-elevated p-4 shadow-md sm:p-6 dark:shadow-none" data-testid="gallery-preview">
        <Suspense>
          <component :is="definition.component" />
          <template #fallback><USkeleton class="h-72 w-full rounded-xl" /></template>
        </Suspense>
      </div>
    </div>
    <dl class="grid gap-3 rounded-xl border border-content-border p-4 sm:grid-cols-2">
      <div>
        <dt class="font-medium text-content-heading">Source</dt>
        <dd class="text-base text-content-body sm:text-sm">{{ definition.source }}</dd>
      </div>
      <div>
        <dt class="font-medium text-content-heading">Intentional divergence</dt>
        <dd class="text-base text-content-body sm:text-sm">{{ definition.divergence }}</dd>
      </div>
    </dl>
  </section>
</template>
