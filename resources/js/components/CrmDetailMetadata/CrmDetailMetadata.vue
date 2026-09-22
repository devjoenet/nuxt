<script lang="ts" setup>
import { Link } from "@inertiajs/vue3";
import { cn } from "@/lib/utils";
import type { CrmDetailMetadataItem } from "@/types/crm";

const props = defineProps<{
  items: CrmDetailMetadataItem[];
  layout?: "stacked" | "rows" | "grid";
  class?: string;
}>();
</script>

<template>
  <dl :class="cn(layout === 'rows' && 'divide-y divide-default border-y border-default', layout === 'grid' && 'grid gap-4 border-y border-default py-5 sm:grid-cols-2 lg:grid-cols-4', (!layout || layout === 'stacked') && 'space-y-4', props.class)">
    <div v-for="item in items" :key="item.label" :class="layout === 'rows' && 'grid gap-1 py-4 sm:grid-cols-[10rem_minmax(0,1fr)] sm:gap-4'">
      <dt class="text-base text-muted sm:text-sm">{{ item.label }}</dt>
      <dd :class="layout !== 'rows' && 'mt-1'">
        <Link v-if="item.to" :href="item.to" class="font-medium break-words text-highlighted hover:underline">
          {{ item.value }}
        </Link>
        <a v-else-if="item.href" :href="item.href" :rel="item.external ? 'noreferrer' : undefined" :target="item.external ? '_blank' : undefined" class="font-medium break-words text-highlighted hover:underline">
          {{ item.value }}
        </a>
        <span v-else class="font-medium break-words text-highlighted">{{ item.value }}</span>
      </dd>
    </div>
  </dl>
</template>
