<script setup lang="ts">
import { FormInput } from "@/components/Ui/FormInput";
import { FormSelect } from "@/components/Ui/FormSelect";
import type { ArgonGalleryExample, ArgonGalleryFamily } from "@/types/argonGallery";
import GalleryExampleCard from "./GalleryExampleCard.vue";
import { useGalleryFilters } from "./useGalleryFilters";

const props = defineProps<{ examples: ArgonGalleryExample[]; familyCounts: Record<ArgonGalleryFamily, number> }>();
const { family, familyItems, filtered, groups, query, status, statusItems } = useGalleryFilters(props.examples, props.familyCounts);
</script>

<template>
  <section id="manifest-index" aria-labelledby="manifest-index-title" class="grid scroll-mt-28 gap-5">
    <header>
      <h2 id="manifest-index-title" class="text-xl font-semibold text-balance text-content-heading">Source manifest</h2>
      <p class="max-w-[70ch] text-base text-pretty text-content-body sm:text-sm">Search every licensed example by title, path, ID, or recorded behavior. Filters stay local, so the reference source never becomes a public endpoint.</p>
    </header>
    <div class="grid gap-3 lg:grid-cols-[minmax(0,2fr)_minmax(12rem,1fr)_minmax(12rem,1fr)]">
      <FormInput v-model="query" icon="i-ph-magnifying-glass" label="Search manifest" name="gallery-search" type="search" />
      <FormSelect v-model="family" :items="familyItems" label="Family" name="gallery-family" />
      <FormSelect v-model="status" :items="statusItems" label="Source status" name="gallery-status" />
    </div>
    <p aria-live="polite" class="text-base text-content-subtle sm:text-sm">{{ filtered.length }} of {{ examples.length }} entries shown.</p>
    <div v-if="groups.length" class="grid gap-8">
      <section v-for="group in groups" :key="group.family" :aria-labelledby="`gallery-family-${group.family}`" class="grid gap-3">
        <div class="flex items-baseline justify-between gap-3 border-b border-content-border pb-2">
          <h3 :id="`gallery-family-${group.family}`" class="text-lg font-semibold text-balance text-content-heading">{{ group.label }}</h3>
          <p class="text-base text-content-subtle tabular-nums sm:text-sm">{{ group.examples.length }}</p>
        </div>
        <div class="@container">
          <div class="grid gap-4 @2xl:grid-cols-2 @5xl:grid-cols-3"><GalleryExampleCard v-for="example in group.examples" :key="example.id" :example="example" /></div>
        </div>
      </section>
    </div>
    <UAlert v-else color="neutral" description="Try a broader search or clear one of the filters." title="No manifest entries match" variant="subtle" />
  </section>
</template>
