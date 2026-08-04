<script setup lang="ts">
import { Link } from "@inertiajs/vue3";
import { index as leadsIndex, show as leadShow } from "@/routes/crm/leads";

defineProps<{
  canViewCrm: boolean;
  stats: { label: string; value: number; icon: string }[];
  recentLeads: { uuid: string; name: string; organization: string | null; status: string; created_at: string | null }[];
  upcomingTasks: { uuid: string; title: string; lead_uuid: string; lead_name: string; due_at: string; is_overdue: boolean }[];
}>();
</script>

<template>
  <WorkspacePage id="workspace-dashboard" description="A practical view of the work that needs attention." title="Workspace">
    <template v-if="canViewCrm">
      <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <UPageCard v-for="stat in stats" :key="stat.label" :description="stat.label" variant="subtle">
          <template #leading><UIcon :name="stat.icon" class="size-5 text-primary" /></template>
          <p class="text-3xl font-semibold text-highlighted">{{ stat.value }}</p>
        </UPageCard>
      </div>

      <div class="mt-6 grid gap-6 xl:grid-cols-2">
        <UPageCard description="The latest records entering the CRM." title="Recent leads" variant="subtle">
          <div v-if="recentLeads.length" class="divide-y divide-default">
            <Link v-for="lead in recentLeads" :key="lead.uuid" :href="leadShow.url(lead.uuid)" class="flex items-center justify-between gap-4 py-3 first:pt-0 last:pb-0 hover:text-primary">
              <div class="min-w-0">
                <p class="truncate font-medium text-highlighted">{{ lead.name }}</p>
                <p class="truncate text-sm text-muted">{{ lead.organization ?? "Individual contact" }}</p>
              </div>
              <UBadge :label="lead.status" color="neutral" variant="subtle" />
            </Link>
          </div>
          <div v-else class="py-4 text-sm text-muted">No leads have been captured yet.</div>
          <template #footer><Link :href="leadsIndex.url()" class="text-sm font-semibold text-primary hover:underline">Open CRM</Link></template>
        </UPageCard>

        <UPageCard description="Open tasks ordered by the next action date." title="Follow-ups" variant="subtle">
          <div v-if="upcomingTasks.length" class="divide-y divide-default">
            <Link v-for="task in upcomingTasks" :key="task.uuid" :href="leadShow.url(task.lead_uuid)" class="flex items-center justify-between gap-4 py-3 first:pt-0 last:pb-0 hover:text-primary">
              <div class="min-w-0">
                <p class="truncate font-medium text-highlighted">{{ task.title }}</p>
                <p class="truncate text-sm text-muted">{{ task.lead_name }}</p>
              </div>
              <UBadge :color="task.is_overdue ? 'error' : 'neutral'" :label="new Date(task.due_at).toLocaleDateString()" variant="subtle" />
            </Link>
          </div>
          <div v-else class="py-4 text-sm text-muted">No open follow-ups need attention.</div>
        </UPageCard>
      </div>
    </template>

    <UPageCard v-else description="Your account is active, but no CRM permission has been assigned yet. Ask an administrator to update your role." icon="i-ph-shield-check" title="Workspace access is being prepared" variant="subtle" />
  </WorkspacePage>
</template>
