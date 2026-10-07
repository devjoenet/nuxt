<script lang="ts" setup>
import { Stat } from "@/components/Ui/Stat";

defineProps<{
  queue: { connection: string; default: string; production_driver: string };
  queues: { connection: string; name: string; pending: number | null }[];
  stats: { label: string; value: number | null }[];
  links: { label: string; description: string; url: string }[];
}>();
</script>

<template>
  <WorkspacePage id="admin-jobs" description="Queue health, Horizon monitoring, and operational links" document-title="Jobs and queues" title="Jobs & queues">
    <div class="grid gap-4 sm:grid-cols-3">
      <Stat v-for="stat in stats" :key="stat.label" :label="stat.label" tone="info" :value="stat.value ?? 'Unavailable'" />
    </div>

    <UAlert
      class="mt-3 overflow-visible"
      color="neutral"
      description="Use the failed-jobs view to inspect and retry failures. Destructive queue and supervisor controls remain behind jobs.manage."
      icon="i-ph-shield-check"
      title="Operational access is permission-protected"
      variant="subtle"
    />

    <UAlert :title="`Current queue: ${queue.connection} / ${queue.default}`" class="mt-3 overflow-visible" color="info" description="Local development uses the database queue. Staging and production use Redis with Horizon." icon="i-ph-info" orientation="horizontal" variant="subtle" />

    <AppPageCard class="mt-3" title="Pending jobs by queue" description="Jobs waiting for a worker, excluding delayed and running jobs. Queue names follow the Horizon supervisor configuration; local counts use the current queue connection.">
      <UAlert v-if="stats[0]?.value === null" color="warning" title="Queue counts unavailable" description="A queue connection could not be reached. The total is unavailable until all monitored queues can be read." variant="subtle" />
      <dl class="grid gap-4 sm:grid-cols-3">
        <div v-for="item in queues" :key="`${item.connection}:${item.name}`">
          <dt class="text-sm text-muted">{{ item.connection }} / {{ item.name }}</dt>
          <dd class="text-lg font-semibold text-highlighted">{{ item.pending ?? "Unavailable" }}</dd>
        </div>
      </dl>
    </AppPageCard>

    <div class="mt-3 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
      <AppPageCard v-for="link in links" :key="link.label" :description="link.description" :style="'outline'" :title="link.label" class="dark:bg-elevated dark:shadow-none">
        <template #footer>
          <a :href="link.url" class="text-sm font-medium text-primary hover:underline" rel="noreferrer" target="_blank">Open in Horizon <span aria-hidden="true">↗</span></a>
        </template>
      </AppPageCard>
    </div>
  </WorkspacePage>
</template>
