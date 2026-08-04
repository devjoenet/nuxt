<script lang="ts" setup>
import { Form, Link } from "@inertiajs/vue3";
import FormInput from "@/components/FormInput.vue";
import FormMultiSelect from "@/components/FormMultiSelect.vue";
import { index } from "@/routes/admin/audit";

type FilterOption = { label: string; value: string };

defineProps<{
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
    links: { url: string | null; label: string; active: boolean }[];
  };
  filters: { search: string | null; event: string[]; causer: string[]; subject: string[]; from: string | null; to: string | null };
  eventOptions: FilterOption[];
  causerOptions: FilterOption[];
  subjectOptions: FilterOption[];
}>();
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

    <UPageCard :ui="{ container: 'p-0 sm:p-0' }" class="dark:bg-elevated dark:ring-accented dark:shadow-none" variant="outline">
      <div class="overflow-x-auto">
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
      </div>
      <p v-if="!activities.data.length" class="p-6 text-sm text-muted">No activity matches these filters.</p>
      <nav v-if="activities.links.length > 3" aria-label="Pagination" class="flex flex-wrap gap-1 border-t border-default p-4">
        <Link v-for="link in activities.links" :key="link.label" :class="['rounded-md px-3 py-1 text-sm', link.active ? 'bg-primary text-inverted' : 'text-muted hover:bg-elevated', !link.url && 'pointer-events-none opacity-50']" :href="link.url ?? index()">
          <span v-html="link.label" />
        </Link>
      </nav>
    </UPageCard>
  </WorkspacePage>
</template>
