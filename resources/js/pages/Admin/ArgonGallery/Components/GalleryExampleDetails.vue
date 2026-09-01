<script setup lang="ts">
import { index } from "@/routes/admin/argonGallery";
import type { ArgonGalleryExample } from "@/types/argonGallery";
import { galleryLabel } from "./galleryCatalog";

defineProps<{ example: ArgonGalleryExample }>();
</script>

<template>
  <article class="grid gap-6">
    <header class="grid gap-3">
      <UButton :to="index().url" class="w-fit" color="neutral" icon="i-ph-arrow-left" label="Back to manifest" size="sm" variant="ghost" />
      <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
          <p class="text-base font-medium text-primary sm:text-sm">{{ galleryLabel(example.family) }}</p>
          <h2 class="text-balance text-2xl font-semibold tracking-tight text-content-heading">{{ example.title }}</h2>
        </div>
        <UBadge :color="example.source_status === 'available' ? 'success' : 'warning'" :label="example.source_status" variant="subtle" />
      </div>
    </header>
    <dl class="grid gap-4 rounded-xl border border-content-border p-4 sm:grid-cols-2">
      <div>
        <dt class="font-medium text-content-heading">Manifest ID</dt>
        <dd class="break-words text-base text-content-body sm:text-sm">
          <code>{{ example.id }}</code>
        </dd>
      </div>
      <div>
        <dt class="font-medium text-content-heading">Source path</dt>
        <dd class="break-words text-base text-content-body sm:text-sm">
          <code>{{ example.source_path }}</code>
        </dd>
      </div>
      <div>
        <dt class="font-medium text-content-heading">Layout</dt>
        <dd class="text-base text-content-body sm:text-sm">{{ example.layout }}</dd>
      </div>
      <div>
        <dt class="font-medium text-content-heading">Planned Vue destination</dt>
        <dd class="break-words text-base text-content-body sm:text-sm">
          <code>{{ example.planned_vue_destination }}</code>
        </dd>
      </div>
      <div>
        <dt class="font-medium text-content-heading">Source note</dt>
        <dd class="text-pretty text-base text-content-body sm:text-sm">{{ example.source_note }}</dd>
      </div>
      <div>
        <dt class="font-medium text-content-heading">Intentional divergence</dt>
        <dd class="text-pretty text-base text-content-body sm:text-sm">{{ example.divergence_note }}</dd>
      </div>
    </dl>
    <div class="grid gap-5 lg:grid-cols-2">
      <section aria-labelledby="example-behaviors" class="grid content-start gap-3">
        <h3 id="example-behaviors" class="text-lg font-semibold text-content-heading">Recorded behaviors</h3>
        <ul class="flex flex-wrap gap-2" role="list">
          <li v-for="behavior in example.behaviors" :key="behavior"><UBadge :label="behavior" variant="subtle" /></li>
        </ul>
      </section>
      <section aria-labelledby="example-dependencies" class="grid content-start gap-3">
        <h3 id="example-dependencies" class="text-lg font-semibold text-content-heading">Dependencies</h3>
        <ul class="flex flex-wrap gap-2" role="list">
          <li v-for="dependency in example.dependencies" :key="dependency"><UBadge color="neutral" :label="dependency" variant="outline" /></li>
        </ul>
      </section>
    </div>
    <UAlert color="info" description="No production page consumes this reference yet. Tasks 10–12 own the inert Vue ports; later migration tasks decide whether application pages consume their compositions." title="Consumption status" variant="subtle" />
  </article>
</template>
