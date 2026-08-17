<script lang="ts" setup>
import { formatDate } from "@/lib/dateFormatters";
import { create as requestCreate, index as requestsIndex } from "@/routes/development/requests";
import { index as reviewIndex } from "@/routes/development/review";
import { show } from "@/routes/development/tasks";
import type { DevelopmentTask } from "@/types/development";

defineProps<{
  requests: DevelopmentTask[];
  reviewQueueCount: number;
  roadmapCount: number;
  canCreate: boolean;
  canReview: boolean;
}>();
</script>

<template>
  <WorkspacePage id="development-overview" title="Development">
    <template #actions>
      <UButton v-if="canCreate" :to="requestCreate().url" icon="i-ph-plus" label="Submit request" />
    </template>

    <div class="grid gap-6">
      <div class="grid gap-4 sm:grid-cols-2">
        <AppPageCard v-if="canReview" :description="`${reviewQueueCount} ${reviewQueueCount === 1 ? 'request' : 'requests'} awaiting a decision or clarification.`" :to="reviewIndex().url" icon="i-ph-magnifying-glass" title="Review queue" variant="info" />
        <AppPageCard :description="`${roadmapCount} ${roadmapCount === 1 ? 'task' : 'tasks'} currently planned or underway.`" icon="i-ph-map-trifold" title="Roadmap work" variant="primary" />
      </div>

      <section class="grid gap-4">
        <div class="flex flex-wrap items-end justify-between gap-3">
          <div>
            <h2 class="text-xl font-semibold text-highlighted">My requests</h2>
            <p class="mt-1 text-sm text-muted">Your active requests and their current review status.</p>
          </div>
          <UButton :to="requestsIndex().url" color="neutral" label="View all" variant="ghost" />
        </div>

        <ul v-if="requests.length" class="divide-y divide-default border-y border-default" role="list">
          <li v-for="request in requests" :key="request.id" class="flex flex-wrap items-center justify-between gap-4 py-4">
            <div class="min-w-0">
              <UButton :label="request.title" :to="show(request.id).url" class="px-0 text-left" color="neutral" variant="link" />
              <p class="text-sm text-muted">{{ request.affected_area_label }} · Submitted {{ formatDate(request.submitted_at) }}</p>
            </div>
            <UBadge :label="request.status_label" color="neutral" variant="subtle" />
          </li>
        </ul>

        <UEmpty v-else description="Submit a bug, improvement, feature, or maintenance request when there is work to consider." icon="i-ph-clipboard-text" title="No active requests">
          <template #actions><UButton v-if="canCreate" :to="requestCreate().url" label="Submit request" /></template>
        </UEmpty>
      </section>
    </div>
  </WorkspacePage>
</template>
