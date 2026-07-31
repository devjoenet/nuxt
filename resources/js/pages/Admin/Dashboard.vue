<script lang="ts" setup>
import { Head } from "@inertiajs/vue3";
import Layout from "@/layouts/Default.vue";

defineOptions({ layout: Layout });

defineProps<{
  stats: { label: string; value: number }[];
  recentActivities: { id: number; description: string; causer: string | null; created_at: string | null }[];
  recentUsers: { id: number; name: string; email: string; role: string | null; is_active: boolean }[];
}>();
</script>

<template>
  <Head title="Admin dashboard" />

  <UDashboardPanel id="admin-dashboard">
    <template #header>
      <UDashboardNavbar description="Business health and access control" title="Admin">
        <template #leading><UDashboardSidebarCollapse /></template>
      </UDashboardNavbar>
    </template>

    <template #body>
      <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
        <UPageCard v-for="stat in stats" :key="stat.label" :description="stat.label" variant="subtle">
          <p class="text-3xl font-semibold text-highlighted">{{ stat.value }}</p>
        </UPageCard>
      </div>

      <div class="mt-6 grid gap-6 xl:grid-cols-2">
        <UPageCard description="The latest auditable workspace changes." title="Recent activity" variant="subtle">
          <ul v-if="recentActivities.length" class="divide-y divide-default">
            <li v-for="activity in recentActivities" :key="activity.id" class="py-3 first:pt-0 last:pb-0">
              <p class="text-sm text-highlighted">{{ activity.description }}</p>
              <p class="text-xs text-muted">{{ activity.causer ?? "System" }}</p>
            </li>
          </ul>
          <p v-else class="text-sm text-muted">No activity has been recorded yet.</p>
        </UPageCard>

        <UPageCard description="Access status for the newest workspace members." title="Recent users" variant="subtle">
          <ul class="divide-y divide-default">
            <li v-for="user in recentUsers" :key="user.id" class="flex items-center justify-between gap-3 py-3 first:pt-0 last:pb-0">
              <div>
                <p class="text-sm font-medium text-highlighted">{{ user.name }}</p>
                <p class="text-xs text-muted">{{ user.email }}</p>
              </div>
              <UBadge :color="user.is_active ? 'success' : 'neutral'" variant="subtle">{{ user.is_active ? "Active" : "Inactive" }}</UBadge>
            </li>
          </ul>
        </UPageCard>
      </div>
    </template>
  </UDashboardPanel>
</template>
