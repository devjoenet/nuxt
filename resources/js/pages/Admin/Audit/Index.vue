<script lang="ts" setup>
import { Form, router } from "@inertiajs/vue3";
import { FormInput } from "@/components/Ui/FormInput";
import { FormMultiSelect } from "@/components/Ui/FormMultiSelect";
import { IndexEmptyState } from "@/components/IndexEmptyState";
import { IndexPagination } from "@/components/IndexPagination";
import { IndexTableFrame } from "@/components/IndexTableFrame";
import { index } from "@/routes/admin/audit";
import type { PaginationMeta } from "@/types/pagination";

type FilterOption = { label: string; value: string };

const props = defineProps<{
  activities: {
    data: {
      id: number;
      description: string;
      event: string | null;
      log_name: string | null;
      causer: string | null;
      subject: string | null;
      changes: Record<string, unknown> | null;
      created_at: string | null;
    }[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
  filters: { search: string | null; event: string[]; causer: string[]; subject: string[]; from: string | null; to: string | null };
  eventOptions: FilterOption[];
  causerOptions: FilterOption[];
  subjectOptions: FilterOption[];
}>();

const activityMeta = computed<PaginationMeta>(() => ({
  current_page: props.activities.current_page,
  last_page: props.activities.last_page,
  per_page: props.activities.per_page,
  total: props.activities.total,
}));

const visitPage = (page: number): void => {
  router.get(index().url, { ...props.filters, page }, { preserveScroll: true, preserveState: true });
};
</script>

<template>
  <WorkspacePage id="admin-audit" :toolbar-ui="{ root: 'overflow-visible' }" description="Safe before-and-after visibility for sensitive workspace changes." title="Audit log" toolbar-class="audit-toolbar">
    <template #toolbar>
      <Form class="grid w-full grid-cols-1 gap-2 sm:grid-cols-2 xl:grid-cols-[minmax(16rem,1.4fr)_minmax(12rem,1fr)_minmax(12rem,1fr)_minmax(12rem,1fr)_9rem_9rem_auto]" v-bind="index.form()">
        <FormInput id="audit-search" :default-value="filters.search ?? undefined" icon="i-ph-magnifying-glass" label="Search activity" name="search" size="sm" />
        <FormMultiSelect id="audit-event" :default-value="filters.event" :items="eventOptions" label="Event" name="event" placeholder="All events" size="sm" />
        <FormMultiSelect id="audit-causer" :default-value="filters.causer" :items="causerOptions" label="Causer" name="causer" placeholder="All causers" size="sm" />
        <FormMultiSelect id="audit-subject" :default-value="filters.subject" :items="subjectOptions" label="Subject" name="subject" placeholder="All subject types" size="sm" />
        <FormInput id="audit-from" :default-value="filters.from ?? undefined" label="From" name="from" size="sm" type="date" />
        <FormInput id="audit-to" :default-value="filters.to ?? undefined" label="To" name="to" size="sm" type="date" />
        <UButton class="w-full sm:col-span-2 xl:col-span-1" label="Filter" type="submit" variant="soft" />
      </Form>
    </template>

    <AppPageCard :style="'outline'" :ui="{ container: 'p-0 sm:p-0' }" class="dark:bg-elevated dark:shadow-none">
      <IndexTableFrame v-if="activities.data.length" contained>
        <table class="min-w-full text-sm">
          <thead class="text-left text-xs text-muted">
            <tr>
              <th class="px-4 py-3 font-medium">Description</th>
              <th class="px-4 py-3 font-medium">Causer</th>
              <th class="px-4 py-3 font-medium">Subject</th>
              <th class="px-4 py-3 font-medium">Changes</th>
              <th class="px-4 py-3 font-medium">When</th>
            </tr>
          </thead>
          <tbody class="striped-table-body divide-y divide-default">
            <tr v-for="activity in activities.data" :key="activity.id">
              <td class="px-4 py-3">
                <p class="font-medium text-highlighted">{{ activity.description }}</p>
                <p class="text-xs text-muted">{{ activity.log_name }}{{ activity.event ? ` · ${activity.event}` : "" }}</p>
              </td>
              <td class="px-4 py-3 text-muted">{{ activity.causer ?? "System" }}</td>
              <td class="px-4 py-3 text-muted">{{ activity.subject ?? "—" }}</td>
              <td class="max-w-sm px-4 py-3 text-xs text-muted">
                <pre v-if="activity.changes" class="whitespace-pre-wrap">{{ JSON.stringify(activity.changes, null, 2) }}</pre>
                <span v-else>—</span>
              </td>
              <td class="px-4 py-3 text-muted">{{ activity.created_at ? new Date(activity.created_at).toLocaleString() : "—" }}</td>
            </tr>
          </tbody>
        </table>
      </IndexTableFrame>
      <IndexEmptyState v-else class="p-6" description="No activity matches these filters." icon="i-ph-magnifying-glass" title="No audit activity found" />

      <IndexPagination :meta="activityMeta" class="p-4" item-label="activities" @page="visitPage" />
    </AppPageCard>
  </WorkspacePage>
</template>
