<script lang="ts" setup>
import { router } from "@inertiajs/vue3";
import Layout from "@/layouts/Default.vue";
import { create, index } from "@/routes/development/requests";
import { show } from "@/routes/development/tasks";
import type { PaginatedDevelopmentTasks } from "@/types/development";

defineOptions({ layout: Layout });
defineProps<{ requests: PaginatedDevelopmentTasks; canCreate: boolean }>();

const formatDate = (value: string): string => new Intl.DateTimeFormat("en-US", { dateStyle: "medium" }).format(new Date(value));
</script>

<template>
  <UDashboardPanel id="development-requests">
    <template #header>
      <UDashboardNavbar title="My requests">
        <template #leading><UDashboardSidebarCollapse /></template>
        <template #right><UButton v-if="canCreate" :to="create().url" icon="i-ph-plus" label="Submit request" /></template>
      </UDashboardNavbar>
    </template>

    <template #body>
      <div class="grid gap-4">
        <ul v-if="requests.data.length" class="grid gap-3" role="list">
          <li v-for="request in requests.data" :key="request.id">
            <UPageCard :to="show(request.id).url" variant="subtle">
              <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                  <h2 class="font-semibold text-highlighted">{{ request.title }}</h2>
                  <p class="mt-1 text-sm text-muted">{{ request.request_type_label }} · {{ request.affected_area_label }} · Submitted {{ formatDate(request.submitted_at) }}</p>
                </div>
                <UBadge :label="request.status_label" color="neutral" variant="subtle" />
              </div>
              <p class="mt-3 line-clamp-2 text-sm leading-6 text-muted">{{ request.description }}</p>
            </UPageCard>
          </li>
        </ul>

        <UEmpty v-else description="Your active development requests will appear here." icon="i-ph-clipboard-text" title="No active requests">
          <template #actions><UButton v-if="canCreate" :to="create().url" label="Submit request" /></template>
        </UEmpty>

        <div v-if="requests.meta.last_page > 1" class="flex items-center justify-between border-t border-default pt-4">
          <p class="text-sm text-muted">{{ requests.meta.total }} requests</p>
          <UPagination :items-per-page="requests.meta.per_page" :page="requests.meta.current_page" :total="requests.meta.total" @update:page="router.get(index({ query: { page: $event } }).url)" />
        </div>
      </div>
    </template>
  </UDashboardPanel>
</template>
