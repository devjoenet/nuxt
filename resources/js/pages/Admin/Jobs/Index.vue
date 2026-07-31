<script lang="ts" setup>
import { Head } from "@inertiajs/vue3";
import Layout from "@/layouts/Default.vue";

defineOptions({ layout: Layout });

defineProps<{
  queue: { connection: string; default: string; production_driver: string };
  stats: { label: string; value: number }[];
  links: { label: string; description: string; url: string }[];
}>();
</script>

<template>
  <Head title="Jobs and queues" />

  <UDashboardPanel id="admin-jobs">
    <template #header>
      <UDashboardNavbar description="Queue health, Horizon monitoring, and operational links" title="Jobs & queues">
        <template #leading><UDashboardSidebarCollapse /></template>
      </UDashboardNavbar>
    </template>

    <template #body>
      <div class="grid gap-4 sm:grid-cols-3">
        <UPageCard v-for="stat in stats" :key="stat.label" :description="stat.label" variant="subtle">
          <p class="text-3xl font-semibold text-highlighted">{{ stat.value }}</p>
        </UPageCard>
      </div>

      <UAlert
        class="mt-3 overflow-visible"
        color="neutral"
        description="Use the failed-jobs view to inspect and retry failures. Destructive queue and supervisor controls remain behind jobs.manage."
        icon="i-ph-shield-check"
        title="Operational access is permission-protected"
        variant="subtle"
      />

      <UAlert
        :title="`Current queue: ${queue.connection} / ${queue.default}`"
        class="mt-3 overflow-visible"
        color="info"
        description="Local development uses the database queue. Staging and production use Redis with Horizon."
        icon="i-ph-info"
        variant="subtle"
        orientation="horizontal"
      />

      <div class="mt-3 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        <UPageCard v-for="link in links" :key="link.label" :description="link.description" :title="link.label" variant="subtle">
          <template #footer>
            <a :href="link.url" class="text-sm font-medium text-primary hover:underline" rel="noreferrer" target="_blank">Open in Horizon <span aria-hidden="true">↗</span></a>
          </template>
        </UPageCard>
      </div>
    </template>
  </UDashboardPanel>
</template>
