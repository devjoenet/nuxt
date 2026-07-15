<script lang="ts" setup>
  import { Form, Head, Link } from "@inertiajs/vue3";
  import FormInput from "@/components/FormInput.vue";
  import Layout from "@/layouts/Default.vue";
  import { index } from "@/routes/admin/audit";

  defineOptions({ layout: Layout });
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
    filters: { search?: string; event?: string; causer?: string };
  }>();
</script>

<template>
  <Head title="Audit log" />

  <UDashboardPanel id="admin-audit">
    <template #header>
      <UDashboardNavbar description="Safe before-and-after visibility for sensitive workspace changes." title="Audit log">
        <template #leading><UDashboardSidebarCollapse /></template>
      </UDashboardNavbar>
      <UDashboardToolbar>
        <Form class="flex w-full flex-wrap gap-2" v-bind="index.form()">
          <FormInput :default-value="filters.search" icon="i-ph-magnifying-glass" label="Search activity" name="search" size="sm" />
          <FormInput :default-value="filters.event" label="Event" name="event" size="sm" />
          <FormInput :default-value="filters.causer" label="Causer" name="causer" size="sm" />
          <UButton label="Filter" type="submit" variant="soft" />
        </Form>
      </UDashboardToolbar>
    </template>

    <template #body>
      <UPageCard :ui="{ container: 'p-0 sm:p-0' }" variant="subtle">
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
            <tbody class="divide-y divide-default">
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
          <Link
            v-for="link in activities.links"
            :key="link.label"
            :class="['rounded-md px-3 py-1 text-sm', link.active ? 'bg-primary text-inverted' : 'text-muted hover:bg-elevated', !link.url && 'pointer-events-none opacity-50']"
            :href="link.url ?? index()"
          >
            <span v-html="link.label" />
          </Link>
        </nav>
      </UPageCard>
    </template>
  </UDashboardPanel>
</template>
