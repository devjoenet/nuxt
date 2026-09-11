<script lang="ts" setup>
import { Stat } from "@/components/Ui/Stat";

defineProps<{
  stats: { label: string; value: number }[];
  recentActivities: { id: number; description: string; causer: string | null; created_at: string | null }[];
  recentUsers: { id: number; name: string; email: string; role: string | null; is_active: boolean }[];
}>();
</script>

<template>
  <WorkspacePage id="admin-dashboard" description="Business health and access control" document-title="Admin dashboard" title="Admin">
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
      <Stat v-for="stat in stats" :key="stat.label" :label="stat.label" tone="secondary" :value="stat.value" />
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-2">
      <AppPageCard :style="'outline'" class="dark:bg-elevated dark:shadow-none" description="The latest auditable workspace changes." title="Recent activity">
        <ul v-if="recentActivities.length" class="divide-y divide-default">
          <li v-for="activity in recentActivities" :key="activity.id" class="py-3 first:pt-0 last:pb-0">
            <p class="text-sm text-highlighted">{{ activity.description }}</p>
            <p class="text-xs text-muted">{{ activity.causer ?? "System" }}</p>
          </li>
        </ul>
        <p v-else class="text-sm text-muted">No activity has been recorded yet.</p>
      </AppPageCard>

      <AppPageCard :style="'outline'" class="dark:bg-elevated dark:shadow-none" description="Access status for the newest workspace members." title="Recent users">
        <ul class="divide-y divide-default">
          <li v-for="user in recentUsers" :key="user.id" class="flex items-center justify-between gap-3 py-3 first:pt-0 last:pb-0">
            <div>
              <p class="text-sm font-medium text-highlighted">{{ user.name }}</p>
              <p class="text-xs text-muted">{{ user.email }}</p>
            </div>
            <UBadge :color="user.is_active ? 'success' : 'neutral'" variant="subtle">{{ user.is_active ? "Active" : "Inactive" }}</UBadge>
          </li>
        </ul>
      </AppPageCard>
    </div>
  </WorkspacePage>
</template>
