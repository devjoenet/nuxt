<script lang="ts" setup>
import { cn } from "@/lib/utils";
import type { PaginationMeta } from "@/types/pagination";

const props = defineProps<{
  class?: string;
  itemLabel: string;
  meta: PaginationMeta;
}>();

defineEmits<{
  page: [page: number];
}>();
</script>

<template>
  <div v-if="meta.last_page > 1" :class="cn('flex items-center justify-between gap-4 border-t border-default pt-4', props.class)" data-testid="index-pagination">
    <p class="text-base text-muted sm:text-sm">{{ meta.total }} {{ itemLabel }}</p>
    <UPagination :aria-label="`${itemLabel} pagination`" :items-per-page="meta.per_page" :page="meta.current_page" :total="meta.total" @update:page="$emit('page', $event)" />
  </div>
</template>
