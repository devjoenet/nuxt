<script lang="ts" setup>
import type { FormDataConvertible } from "@inertiajs/core";
import { Form } from "@inertiajs/vue3";
import FormInput from "@/components/FormInput.vue";
import FormSelect from "@/components/FormSelect.vue";
import InlineEditActions from "@/components/InlineEditActions.vue";
import { cn } from "@/lib/utils";
import { update as updateRoadmap } from "@/routes/development/tasks/roadmap";
import DevelopmentTaskSummaryField from "@/sections/Development/Tasks/DevelopmentTaskSummaryField.vue";
import type { DevelopmentOption, DevelopmentTask } from "@/types/development";

const props = defineProps<{
  task: DevelopmentTask;
  assignees: DevelopmentOption[];
  priorities: DevelopmentOption[];
  plannedTaskCount: number;
  editable: boolean;
  class?: string;
}>();

const unassignedAssigneeValue = "unassigned";
const assigneeOptions = computed(() => [{ label: "Unassigned", value: unassignedAssigneeValue }, ...props.assignees]);
type RoadmapSummaryField = "assignee" | "priority" | "roadmap_position";
const activeEditor = ref<RoadmapSummaryField | null>(null);
const closeEditor = (): void => {
  activeEditor.value = null;
};
const transformForm = (data: Record<string, FormDataConvertible>): Record<string, FormDataConvertible> => {
  const destination = data.roadmap_destination;
  const requestedPosition = destination === "start" ? 1 : destination === "end" ? props.plannedTaskCount : (data.roadmap_position ?? props.task.roadmap_position);
  const requestedAssignee = data.assignee_id ?? props.task.assignee_id;

  return {
    priority: data.priority ?? props.task.priority,
    assignee_id: requestedAssignee === unassignedAssigneeValue ? null : requestedAssignee,
    roadmap_position: props.task.status === "planned" ? requestedPosition : null,
  };
};
</script>

<template>
  <dl :class="cn('grid gap-6 @2xl:grid-cols-2 @5xl:grid-cols-4', props.class)">
    <DevelopmentTaskSummaryField label="Reviewer">
      {{ task.reviewer ?? "Unassigned" }}
    </DevelopmentTaskSummaryField>

    <DevelopmentTaskSummaryField :editable="editable" :editing="activeEditor === 'assignee'" edit-label="Edit assignee" label="Assignee" @edit="activeEditor = 'assignee'">
      {{ task.assignee ?? "Unassigned" }}
      <template #editor>
        <Form v-slot="{ errors, processing }" :options="{ preserveScroll: true }" :transform="transformForm" class="grid min-w-0 grid-cols-[minmax(0,1fr)_auto_auto] items-start gap-2" v-bind="updateRoadmap.form(task.id)" @success="closeEditor">
          <FormSelect :default-value="task.assignee_id ?? unassignedAssigneeValue" :error="errors.assignee_id" :items="assigneeOptions" aria-label="Assignee" class="min-w-0" name="assignee_id" />
          <InlineEditActions :loading="processing" cancel-label="Cancel assignee edit" @cancel="closeEditor" />
        </Form>
      </template>
    </DevelopmentTaskSummaryField>

    <DevelopmentTaskSummaryField :editable="editable" :editing="activeEditor === 'priority'" edit-label="Edit priority" label="Priority" @edit="activeEditor = 'priority'">
      {{ task.priority_label }}
      <template #editor>
        <Form v-slot="{ errors, processing }" :options="{ preserveScroll: true }" :transform="transformForm" class="grid min-w-0 grid-cols-[minmax(0,1fr)_auto_auto] items-start gap-2" v-bind="updateRoadmap.form(task.id)" @success="closeEditor">
          <FormSelect :default-value="task.priority" :error="errors.priority" :items="priorities" aria-label="Priority" class="min-w-0" name="priority" required />
          <InlineEditActions :loading="processing" cancel-label="Cancel priority edit" @cancel="closeEditor" />
        </Form>
      </template>
    </DevelopmentTaskSummaryField>

    <DevelopmentTaskSummaryField
      :editable="editable && task.status === 'planned'"
      :editing="activeEditor === 'roadmap_position'"
      edit-label="Edit roadmap position"
      label="Roadmap position"
      value-class="tabular-nums text-base/7 text-muted sm:text-sm/6"
      @edit="activeEditor = 'roadmap_position'"
    >
      {{ task.roadmap_position ? `#${task.roadmap_position}` : "Not scheduled" }}
      <template #editor>
        <Form v-slot="{ errors, processing }" :options="{ preserveScroll: true }" :transform="transformForm" class="grid min-w-0 gap-3" v-bind="updateRoadmap.form(task.id)" @success="closeEditor">
          <div class="grid min-w-0 grid-cols-[minmax(0,1fr)_auto_auto] items-start gap-2">
            <FormInput :default-value="task.roadmap_position ?? plannedTaskCount" :error="errors.roadmap_position" :max="plannedTaskCount" aria-label="Roadmap position" class="min-w-0" min="1" name="roadmap_position" required type="number" />
            <InlineEditActions :loading="processing" cancel-label="Cancel roadmap position edit" @cancel="closeEditor" />
          </div>
          <div class="flex flex-wrap gap-2">
            <UButton :disabled="processing || task.roadmap_position === 1" color="neutral" label="Move to start" name="roadmap_destination" size="xs" type="submit" value="start" variant="outline" />
            <UButton :disabled="processing || task.roadmap_position === plannedTaskCount" color="neutral" label="Move to end" name="roadmap_destination" size="xs" type="submit" value="end" variant="outline" />
          </div>
        </Form>
      </template>
    </DevelopmentTaskSummaryField>
  </dl>
</template>
