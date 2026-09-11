<script lang="ts" setup>
import { Stat } from "@/components/Ui/Stat";

defineProps<{
  queue: { connection: string; default: string; production_driver: string };
  stats: { label: string; value: number }[];
  links: { label: string; description: string; url: string }[];
}>();
</script>

<template>
  <WorkspacePage id="admin-jobs" description="Queue health, Horizon monitoring, and operational links" document-title="Jobs and queues" title="Jobs & queues">
    <div class="grid gap-4 sm:grid-cols-3">
      <Stat v-for="stat in stats" :key="stat.label" :label="stat.label" tone="info" :value="stat.value" />
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

    <div class="mt-3 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
      <AppPageCard v-for="link in links" :key="link.label" :description="link.description" :style="'outline'" :title="link.label" class="dark:bg-elevated dark:shadow-none">
        <template #footer>
          <a :href="link.url" class="text-sm font-medium text-primary hover:underline" rel="noreferrer" target="_blank">Open in Horizon <span aria-hidden="true">↗</span></a>
        </template>
      </AppPageCard>
    </div>
  </WorkspacePage>
</template>
