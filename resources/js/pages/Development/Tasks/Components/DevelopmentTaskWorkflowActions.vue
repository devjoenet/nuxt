<script setup lang="ts">
import { cn } from "@/lib/utils";
import DevelopmentTaskProgressAction from "@/pages/Development/Tasks/Components/DevelopmentTaskProgressAction.vue";
import DevelopmentTaskReviewActions from "@/pages/Development/Tasks/Components/DevelopmentTaskReviewActions.vue";
import DevelopmentTaskTestingRequestForm from "@/pages/Development/Tasks/Components/DevelopmentTaskTestingRequestForm.vue";
import type { DevelopmentOption, DevelopmentProgressAction, DevelopmentTask } from "@/types/development";

const props = defineProps<{
  task: DevelopmentTask;
  testers: DevelopmentOption[];
  class?: string;
}>();

const canStartReview = computed(() => props.task.can.review && ["submitted", "needs_information"].includes(props.task.status));
const canRequestInformation = computed(() => props.task.can.review && props.task.status === "submitted");
const canDecide = computed(() => props.task.can.review && props.task.status === "under_review");
const progressAction = computed<DevelopmentProgressAction | null>(() => {
  if (props.task.status === "planned") return { label: "Start work", status: "in_progress", color: "primary" };
  if (props.task.status === "in_progress") return { label: "Mark blocked", status: "blocked", color: "warning" };
  if (props.task.status === "blocked") return { label: "Resume work", status: "in_progress", color: "primary" };
  if (props.task.status === "changes_requested") return { label: "Resume implementation", status: "in_progress", color: "primary" };

  return null;
});
const showReviewActions = computed(() => canStartReview.value || canRequestInformation.value || canDecide.value);
const showProgressAction = computed(() => props.task.can.update_progress && progressAction.value !== null);
const showActions = computed(() => showReviewActions.value || showProgressAction.value || props.task.can.request_testing);
</script>

<template>
  <div v-if="showActions" :class="cn(props.class)">
    <DevelopmentTaskReviewActions v-if="showReviewActions" :can-decide="canDecide" :can-request-information="canRequestInformation" :can-start-review="canStartReview" :task-id="task.id" />
    <DevelopmentTaskProgressAction v-if="showProgressAction && progressAction" :action="progressAction" :task-id="task.id" />
    <DevelopmentTaskTestingRequestForm v-if="task.can.request_testing" :default-tester-id="task.assignee_id" :task-id="task.id" :testers="testers" />
  </div>
</template>
