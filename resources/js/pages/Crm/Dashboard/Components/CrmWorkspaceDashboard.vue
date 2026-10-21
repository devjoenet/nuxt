<script setup lang="ts">
import { Stat } from "@/components/Ui/Stat";
import { Link } from "@inertiajs/vue3";
import { index as leadsIndex, show as leadShow } from "@/routes/crm/leads";

defineOptions({ inheritAttrs: false });

defineProps<{
  canViewCrm: boolean;
  stats: { label: string; value: number; icon: string }[];
  recentLeads: { uuid: string; name: string; organization: string | null; status: string; status_label: string; created_at: string | null }[];
  upcomingTasks: { uuid: string; title: string; lead_uuid: string; lead_name: string; due_at: string; is_overdue: boolean }[];
}>();
</script>

<template>
  <div>
    <template v-if="canViewCrm">
      <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <Stat v-for="stat in stats" :key="stat.label" :icon="stat.icon" :label="stat.label" :value="stat.value" />
      </div>

      <div class="mt-6 grid gap-6 xl:grid-cols-2">
        <AppPageCard description="The latest records entering the CRM." title="Recent leads">
          <div v-if="recentLeads.length" class="divide-y divide-default">
            <Link v-for="lead in recentLeads" :key="lead.uuid" :href="leadShow.url(lead.uuid)" class="flex items-center justify-between gap-4 py-3 first:pt-0 last:pb-0 hover:text-primary">
              <div class="min-w-0">
                <p class="truncate font-medium text-highlighted">{{ lead.name }}</p>
                <p class="truncate text-sm text-muted">{{ lead.organization ?? "Individual contact" }}</p>
              </div>
              <UBadge :label="lead.status_label" color="neutral" variant="subtle" />
            </Link>
          </div>
          <div v-else class="py-4 text-sm text-muted">No leads have been captured yet.</div>
          <template #footer><Link :href="leadsIndex.url()" class="text-sm font-semibold text-primary hover:underline">Open CRM</Link></template>
        </AppPageCard>

        <AppPageCard description="Open tasks ordered by the next action date." title="Follow-ups">
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
        </AppPageCard>
      </div>
    </template>

    <AppPageCard v-else description="Your account is active, but no CRM permission has been assigned yet. Ask an administrator to update your role." icon="i-ph-shield-check" title="Workspace access is being prepared" variant="info" />
  </div>
</template>
