<script setup lang="ts">
import { cn } from "@/lib/utils";
import { formatDateTime } from "@/lib/dateFormatters";
import type { Lead } from "@/types/crm";

const props = defineProps<{
  notes: Lead["notes"];
  class?: string;
}>();

const newestNotes = computed(() => [...(props.notes ?? [])].sort((a, b) => Date.parse(b.created_at) - Date.parse(a.created_at)));
</script>

<template>
  <section :class="cn('grid gap-4', props.class)">
    <h2 class="text-xl font-semibold text-highlighted">Notes</h2>
    <ol class="divide-y divide-default" role="list">
      <li v-for="note in newestNotes" :key="note.id" class="grid gap-1 py-4">
        <p class="text-base wrap-anywhere whitespace-pre-wrap text-highlighted sm:text-sm">{{ note.body }}</p>
        <p class="text-base text-muted sm:text-sm">{{ note.author }}</p>
        <time :datetime="note.created_at" class="text-base text-muted sm:text-sm">{{ formatDateTime(note.created_at) }}</time>
      </li>
      <li v-if="!newestNotes.length" class="py-4 text-base text-muted sm:text-sm">No notes yet.</li>
    </ol>
  </section>
</template>
