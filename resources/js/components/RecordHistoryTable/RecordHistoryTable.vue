<script setup lang="ts">
import { IndexEmptyState } from "@/components/IndexEmptyState";
import { IndexPagination } from "@/components/IndexPagination";
import { IndexTableFrame } from "@/components/IndexTableFrame";
import { formatDateTime } from "@/lib/dateFormatters";
import type { HistoryEntry, HistoryPage } from "./types";

defineProps<{ activities: HistoryPage }>();
defineEmits<{ page: [page: number] }>();
const fields = (entry: HistoryEntry): string[] => [...new Set([...Object.keys(entry.changes.old ?? {}), ...Object.keys(entry.changes.attributes ?? {})])];
const label = (field: string): string => field.replaceAll("_", " ").replace(/\b\w/g, (letter) => letter.toUpperCase());
const value = (values: Record<string, unknown> | undefined, field: string): string => {
  if (!values || !(field in values)) return "Not recorded";
  const item = values[field];
  if (item === null) return "None";
  if (typeof item === "object") return JSON.stringify(item);
  return String(item);
};
</script>

<template>
  <div>
    <IndexTableFrame v-if="activities.data.length" contained>
      <table class="min-w-full text-sm">
        <caption class="sr-only">
          Record audit history, newest first
        </caption>
        <thead class="text-left text-xs text-muted">
          <tr>
            <th scope="col" class="px-4 py-3">Action</th>
            <th scope="col" class="px-4 py-3">Actor</th>
            <th scope="col" class="px-4 py-3">When</th>
            <th scope="col" class="px-4 py-3">Changes</th>
          </tr>
        </thead>
        <tbody class="striped-table-body divide-y divide-default">
          <tr v-for="entry in activities.data" :key="entry.id">
            <td class="px-4 py-3">
              <p class="font-medium text-highlighted">{{ entry.subject }} · {{ entry.action }}</p>
            </td>
            <td class="px-4 py-3 text-muted">{{ entry.actor }}</td>
            <td class="px-4 py-3 text-muted">
              <time :datetime="entry.created_at ?? undefined">{{ formatDateTime(entry.created_at) }}</time>
            </td>
            <td class="min-w-64 px-4 py-3 text-muted">
              <dl v-if="fields(entry).length" class="grid gap-3">
                <div v-for="field in fields(entry)" :key="field">
                  <dt class="font-medium text-highlighted">{{ label(field) }}</dt>
                  <dd class="wrap-anywhere whitespace-pre-wrap"><span class="font-medium">Before:</span> {{ value(entry.changes.old, field) }}<br /><span class="font-medium">After:</span> {{ value(entry.changes.attributes, field) }}</dd>
                </div>
              </dl>
              <span v-else>No field changes recorded.</span>
            </td>
          </tr>
        </tbody>
      </table>
    </IndexTableFrame>
    <IndexEmptyState v-else class="p-6" title="No history recorded" description="No audit events are available for this record." icon="i-ph-clock-counter-clockwise" />
    <IndexPagination :meta="activities" class="p-4" item-label="events" @page="$emit('page', $event)" />
  </div>
</template>
