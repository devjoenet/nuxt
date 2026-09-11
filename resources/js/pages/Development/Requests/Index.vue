<script lang="ts" setup>
import { router } from "@inertiajs/vue3";
import { IndexEmptyState } from "@/components/IndexEmptyState";
import { IndexPagination } from "@/components/IndexPagination";
import { formatDate } from "@/lib/dateFormatters";
import { create, index } from "@/routes/development/requests";
import { show } from "@/routes/development/tasks";
import type { PaginatedDevelopmentTasks } from "@/types/development";

defineProps<{ requests: PaginatedDevelopmentTasks; canCreate: boolean }>();

const visitPage = (page: number): void => {
  router.get(index({ query: { page } }).url, {}, { preserveScroll: true, preserveState: true });
};
</script>

<template>
  <WorkspacePage id="development-requests" title="My requests">
    <template #actions>
      <UButton v-if="canCreate" :to="create().url" icon="i-ph-plus" label="Submit request" />
    </template>

    <div class="grid gap-4">
      <ul v-if="requests.data.length" class="grid gap-3" role="list">
        <li v-for="request in requests.data" :key="request.id">
          <AppPageCard :to="show(request.id).url">
            <div class="flex flex-wrap items-start justify-between gap-4">
              <div>
                <h2 class="font-semibold text-highlighted">{{ request.title }}</h2>
                <p class="mt-1 text-sm text-muted">{{ request.request_type_label }} · {{ request.affected_area_label }} · Submitted {{ formatDate(request.submitted_at) }}</p>
              </div>
              <UBadge :label="request.status_label" color="neutral" variant="subtle" />
            </div>
            <p class="mt-3 line-clamp-2 text-sm leading-6 text-muted">{{ request.description }}</p>
          </AppPageCard>
        </li>
      </ul>

      <IndexEmptyState v-else description="Your active development requests will appear here." icon="i-ph-clipboard-text" title="No active requests">
        <template #actions><UButton v-if="canCreate" :to="create().url" label="Submit request" /></template>
      </IndexEmptyState>

      <IndexPagination :meta="requests.meta" item-label="requests" @page="visitPage" />
    </div>
  </WorkspacePage>
</template>
