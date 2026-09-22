<script setup lang="ts">
import { index } from "@/routes/admin/argonGallery";
import type { ArgonGalleryExample } from "@/types/argonGallery";
import { computed } from "vue";
import { resolveComplexExample } from "./complexExamples";
import { galleryLabel } from "./galleryCatalog";
import { resolveGeneralExample } from "./generalExamples";
import { resolveIdentityProjectExample } from "./identityProjectExamples";

const props = defineProps<{ example: ArgonGalleryExample }>();
const referenceExample = computed(() => resolveGeneralExample(props.example.id) ?? resolveIdentityProjectExample(props.example.id) ?? resolveComplexExample(props.example.id));
</script>

<template>
  <article class="grid gap-6">
    <header class="grid gap-3">
      <UButton :to="index().url" class="w-fit" color="neutral" icon="i-ph-arrow-left" label="Back to manifest" size="sm" variant="ghost" />
      <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
          <p class="text-base font-medium text-primary sm:text-sm">{{ galleryLabel(example.family) }}</p>
          <h2 class="text-2xl font-semibold tracking-tight text-balance text-content-heading">{{ example.title }}</h2>
        </div>
        <div class="flex flex-wrap gap-2"><UBadge color="neutral" :label="example.implementation_state" variant="outline" /><UBadge color="neutral" :label="example.source_status" variant="outline" /></div>
      </div>
    </header>
    <section v-if="referenceExample" :aria-label="`${example.title} reference implementation`" class="rounded-2xl border border-content-border bg-surface-elevated p-4 shadow-sm sm:p-6 dark:shadow-none">
      <Suspense>
        <component :is="referenceExample" :key="example.id" />
        <template #fallback
          ><div aria-live="polite" class="grid gap-4"><USkeleton class="h-8 w-48" /><USkeleton class="h-72 w-full rounded-xl" /></div
        ></template>
      </Suspense>
    </section>
    <UAlert v-else-if="example.implementation_state === 'implemented'" color="error" description="The manifest entry has no registered Vue destination." title="Reference implementation missing" />
    <dl class="grid gap-4 rounded-xl border border-content-border p-4 sm:grid-cols-2">
      <div>
        <dt class="font-medium text-content-heading">Manifest ID</dt>
        <dd class="text-base break-words text-content-body sm:text-sm">
          <code>{{ example.id }}</code>
        </dd>
      </div>
      <div>
        <dt class="font-medium text-content-heading">Source path</dt>
        <dd class="text-base break-words text-content-body sm:text-sm">
          <code>{{ example.source_path }}</code>
        </dd>
      </div>
      <div>
        <dt class="font-medium text-content-heading">Layout</dt>
        <dd class="text-base text-content-body sm:text-sm">{{ example.layout }}</dd>
      </div>
      <div>
        <dt class="font-medium text-content-heading">Planned Vue destination</dt>
        <dd class="text-base break-words text-content-body sm:text-sm">
          <code>{{ example.planned_vue_destination }}</code>
        </dd>
      </div>
      <div>
        <dt class="font-medium text-content-heading">Source note</dt>
        <dd class="text-base text-pretty text-content-body sm:text-sm">{{ example.source_note }}</dd>
      </div>
      <div>
        <dt class="font-medium text-content-heading">Intentional divergence</dt>
        <dd class="text-base text-pretty text-content-body sm:text-sm">{{ example.divergence_note }}</dd>
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
    <UAlert
      v-if="example.implementation_state === 'planned'"
      color="info"
      description="No production page consumes this reference yet. Task 12 owns the remaining inert Vue ports; later migration tasks decide whether application pages consume their compositions."
      title="Consumption status"
      variant="subtle"
    />
  </article>
</template>
