<script setup lang="ts">
import { formatDateTime } from "@/lib/dateFormatters";
import { cn } from "@/lib/utils";
import DevelopmentTaskRoadmapSummaryFields from "@/sections/Development/Tasks/DevelopmentTaskRoadmapSummaryFields.vue";
import type { DevelopmentOption, DevelopmentTask } from "@/types/development";

const props = defineProps<{
  task: DevelopmentTask;
  pageContextLabel: string;
  assignees: DevelopmentOption[];
  priorities: DevelopmentOption[];
  plannedTaskCount: number;
  class?: string;
}>();

const canEditRoadmapSummary = computed(() => props.task.can.manage_roadmap && ["planned", "in_progress", "blocked", "changes_requested"].includes(props.task.status));
type StatusColor = "success" | "error" | "warning" | "info" | "primary" | "neutral";
const taskStatusColor = computed<StatusColor>(() => {
  if (props.task.status === "completed") return "success";
  if (["rejected", "cancelled"].includes(props.task.status)) return "error";
  if (["needs_information", "blocked", "changes_requested"].includes(props.task.status)) return "warning";
  if (props.task.status === "under_review") return "info";
  if (["planned", "in_progress"].includes(props.task.status)) return "primary";

  return "neutral";
});
</script>

<template>
  <AppCard
    id="development-task-summary"
    as="section"
    :class="cn('shadow-lg dark:shadow-none', props.class)"
    data-testid="development-task-summary"
    variant="primary"
    :ui="{
      root: 'relative overflow-hidden rounded-2xl',
      header: 'border-b border-default p-0 sm:p-0',
      body: 'p-5 sm:p-6 lg:p-8',
    }"
  >
    <template #header>
      <div class="relative overflow-hidden p-5 sm:p-7 lg:p-8">
        <div aria-hidden="true" class="absolute inset-x-0 top-0 h-1 bg-linear-to-r from-primary via-secondary to-primary" />
        <div class="grid min-w-0 gap-6 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-start">
          <div class="grid gap-3">
            <p class="font-mono text-base tracking-wide text-primary uppercase sm:text-sm">{{ pageContextLabel }}</p>
            <h1 class="max-w-[28ch] wrap-break-word text-3xl font-semibold tracking-tight text-balance text-highlighted sm:text-4xl">
              {{ task.title }}
            </h1>
            <p class="max-w-[70ch] text-base/7 text-pretty text-muted sm:text-sm/6">
              Submitted by {{ task.submitter }} on <span class="tabular-nums">{{ formatDateTime(task.submitted_at) }}</span
              >.
            </p>
          </div>

          <div class="flex flex-wrap items-center gap-2 lg:max-w-72 lg:justify-end">
            <UBadge :color="taskStatusColor" :label="task.status_label" variant="subtle" />
            <UBadge :label="task.request_type_label" color="secondary" variant="soft" />
            <UBadge :label="task.affected_area_label" color="info" variant="soft" />
          </div>
        </div>
      </div>
    </template>

    <div class="@container">
      <DevelopmentTaskRoadmapSummaryFields :assignees="assignees" :editable="canEditRoadmapSummary" :planned-task-count="plannedTaskCount" :priorities="priorities" :task="task" />
    </div>
  </AppCard>
</template>
