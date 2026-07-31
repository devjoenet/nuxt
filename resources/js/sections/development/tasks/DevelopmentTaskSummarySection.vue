<script setup lang="ts">
import type { FormDataConvertible } from "@inertiajs/core";
import { Form } from "@inertiajs/vue3";
import FormInput from "@/components/FormInput.vue";
import FormSelect from "@/components/FormSelect.vue";
import InlineEditActions from "@/components/InlineEditActions.vue";
import { cn } from "@/lib/utils";
import { update as updateRoadmap } from "@/routes/development/tasks/roadmap";
import DevelopmentTaskSummaryField from "@/sections/development/tasks/DevelopmentTaskSummaryField.vue";
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
const unassignedAssigneeValue = "unassigned";
const assigneeOptions = computed(() => [{ label: "Unassigned", value: unassignedAssigneeValue }, ...props.assignees]);
type RoadmapSummaryField = "assignee" | "priority" | "roadmap_position";
const activeRoadmapEditor = ref<RoadmapSummaryField | null>(null);
const editRoadmapSummary = (field: RoadmapSummaryField): void => {
  activeRoadmapEditor.value = field;
};
const closeRoadmapSummaryEditor = (): void => {
  activeRoadmapEditor.value = null;
};
const transformRoadmapSummaryForm = (data: Record<string, FormDataConvertible>): Record<string, FormDataConvertible> => {
  const destination = data.roadmap_destination;
  const requestedPosition = destination === "start" ? 1 : destination === "end" ? props.plannedTaskCount : (data.roadmap_position ?? props.task.roadmap_position);
  const requestedAssignee = data.assignee_id ?? props.task.assignee_id;

  return {
    priority: data.priority ?? props.task.priority,
    assignee_id: requestedAssignee === unassignedAssigneeValue ? null : requestedAssignee,
    roadmap_position: props.task.status === "planned" ? requestedPosition : null,
  };
};
const formatDateTime = (value: string): string => new Intl.DateTimeFormat("en-US", { dateStyle: "medium", timeStyle: "short" }).format(new Date(value));
</script>

<template>
  <UCard
    id="development-task-summary"
    as="section"
    :class="cn('shadow-lg dark:shadow-none', props.class)"
    :ui="{
      root: 'relative overflow-hidden rounded-2xl',
      header: 'border-b border-default p-0 sm:p-0',
      body: 'p-5 sm:p-6 lg:p-8',
    }"
    data-testid="development-task-summary"
    variant="subtle"
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
      <dl class="grid gap-6 @2xl:grid-cols-2 @5xl:grid-cols-4">
        <DevelopmentTaskSummaryField label="Reviewer">
          {{ task.reviewer ?? "Unassigned" }}
        </DevelopmentTaskSummaryField>

        <DevelopmentTaskSummaryField :editable="canEditRoadmapSummary" edit-label="Edit assignee" :editing="activeRoadmapEditor === 'assignee'" label="Assignee" @edit="editRoadmapSummary('assignee')">
          {{ task.assignee ?? "Unassigned" }}
          <template #editor>
            <Form #default="{ errors, processing }" :options="{ preserveScroll: true }" :transform="transformRoadmapSummaryForm" class="grid min-w-0 grid-cols-[minmax(0,1fr)_auto_auto] items-start gap-2" v-bind="updateRoadmap.form(task.id)" @success="closeRoadmapSummaryEditor">
              <FormSelect :default-value="task.assignee_id ?? unassignedAssigneeValue" :error="errors.assignee_id" :items="assigneeOptions" aria-label="Assignee" class="min-w-0" name="assignee_id" />
              <InlineEditActions :loading="processing" cancel-label="Cancel assignee edit" @cancel="closeRoadmapSummaryEditor" />
            </Form>
          </template>
        </DevelopmentTaskSummaryField>

        <DevelopmentTaskSummaryField :editable="canEditRoadmapSummary" edit-label="Edit priority" :editing="activeRoadmapEditor === 'priority'" label="Priority" @edit="editRoadmapSummary('priority')">
          {{ task.priority_label }}
          <template #editor>
            <Form #default="{ errors, processing }" :options="{ preserveScroll: true }" :transform="transformRoadmapSummaryForm" class="grid min-w-0 grid-cols-[minmax(0,1fr)_auto_auto] items-start gap-2" v-bind="updateRoadmap.form(task.id)" @success="closeRoadmapSummaryEditor">
              <FormSelect :default-value="task.priority" :error="errors.priority" :items="priorities" aria-label="Priority" class="min-w-0" name="priority" required />
              <InlineEditActions :loading="processing" cancel-label="Cancel priority edit" @cancel="closeRoadmapSummaryEditor" />
            </Form>
          </template>
        </DevelopmentTaskSummaryField>

        <DevelopmentTaskSummaryField
          :editable="canEditRoadmapSummary && task.status === 'planned'"
          edit-label="Edit roadmap position"
          :editing="activeRoadmapEditor === 'roadmap_position'"
          label="Roadmap position"
          value-class="tabular-nums text-base/7 text-muted sm:text-sm/6"
          @edit="editRoadmapSummary('roadmap_position')"
        >
          {{ task.roadmap_position ? `#${task.roadmap_position}` : "Not scheduled" }}
          <template #editor>
            <Form #default="{ errors, processing }" :options="{ preserveScroll: true }" :transform="transformRoadmapSummaryForm" class="grid min-w-0 gap-3" v-bind="updateRoadmap.form(task.id)" @success="closeRoadmapSummaryEditor">
              <div class="grid min-w-0 grid-cols-[minmax(0,1fr)_auto_auto] items-start gap-2">
                <FormInput :default-value="task.roadmap_position ?? plannedTaskCount" :error="errors.roadmap_position" :max="plannedTaskCount" aria-label="Roadmap position" class="min-w-0" min="1" name="roadmap_position" required type="number" />
                <InlineEditActions :loading="processing" cancel-label="Cancel roadmap position edit" @cancel="closeRoadmapSummaryEditor" />
              </div>
              <div class="flex flex-wrap gap-2">
                <UButton :disabled="processing || task.roadmap_position === 1" color="neutral" label="Move to start" name="roadmap_destination" size="xs" type="submit" value="start" variant="outline" />
                <UButton :disabled="processing || task.roadmap_position === plannedTaskCount" color="neutral" label="Move to end" name="roadmap_destination" size="xs" type="submit" value="end" variant="outline" />
              </div>
            </Form>
          </template>
        </DevelopmentTaskSummaryField>
      </dl>
    </div>
  </UCard>
</template>
