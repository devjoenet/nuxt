<script setup lang="ts">
import { show } from "@/routes/admin/argonGallery/examples";
import type { ArgonGalleryExample } from "@/types/argonGallery";

defineProps<{ example: ArgonGalleryExample }>();
</script>

<template>
  <article class="flex min-w-0 flex-col gap-4 rounded-xl border border-content-border bg-surface-elevated p-4 shadow-xs dark:shadow-none">
    <div class="flex min-w-0 items-start justify-between gap-3">
      <div class="min-w-0">
        <h4 class="text-balance font-semibold text-content-heading">{{ example.title }}</h4>
        <p class="truncate text-base text-content-subtle sm:text-sm">
          <code>{{ example.source_path }}</code>
        </p>
      </div>
      <UBadge :color="example.source_status === 'available' ? 'success' : 'warning'" :label="example.source_status" variant="subtle" />
    </div>
    <p class="text-pretty text-base text-content-body sm:text-sm">{{ example.behaviors.length }} recorded behaviors · {{ example.dependencies.length }} dependencies</p>
    <UButton :to="show(example.id).url" class="self-start" color="neutral" label="Inspect reference" size="sm" variant="outline" />
  </article>
</template>
