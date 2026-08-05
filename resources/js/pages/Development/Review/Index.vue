<script lang="ts" setup>
import { router } from "@inertiajs/vue3";
import IndexEmptyState from "@/components/IndexEmptyState.vue";
import IndexPagination from "@/components/IndexPagination.vue";
import { formatDate } from "@/lib/dateFormatters";
import { index } from "@/routes/development/review";
import { show } from "@/routes/development/tasks";
import type { PaginatedDevelopmentTasks } from "@/types/development";

defineProps<{ requests: PaginatedDevelopmentTasks }>();

const visitPage = (page: number): void => {
  router.get(index({ query: { page } }).url, {}, { preserveScroll: true, preserveState: true });
};
</script>

<template>
  <WorkspacePage id="development-review-queue" title="Review queue">
    <div class="grid gap-4">
      <ul v-if="requests.data.length" class="grid gap-3" role="list">
        <li v-for="request in requests.data" :key="request.id">
          <UPageCard :to="show(request.id).url" variant="subtle">
            <div class="flex flex-wrap items-start justify-between gap-4">
              <div>
                <h2 class="font-semibold text-highlighted">{{ request.title }}</h2>
                <p class="mt-1 text-sm text-muted">{{ request.submitter }} · {{ request.request_type_label }} · {{ request.affected_area_label }} · {{ formatDate(request.submitted_at) }}</p>
              </div>
              <UBadge :color="request.status === 'needs_information' ? 'warning' : request.status === 'under_review' ? 'info' : 'neutral'" :label="request.status_label" variant="subtle" />
            </div>
            <p class="mt-3 line-clamp-2 text-sm leading-6 text-muted">{{ request.description }}</p>
          </UPageCard>
        </li>
      </ul>

      <IndexEmptyState v-else description="There are no submitted, clarification, or under-review requests." icon="i-ph-check-circle" title="Review queue is clear" />

      <IndexPagination :meta="requests.meta" item-label="requests" @page="visitPage" />
    </div>
  </WorkspacePage>
</template>
