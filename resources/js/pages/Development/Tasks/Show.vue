<script lang="ts" setup>
  import type { FormDataConvertible } from "@inertiajs/core";
  import { Form, usePage } from "@inertiajs/vue3";
  import FormInput from "@/components/FormInput.vue";
  import FormSelect from "@/components/FormSelect.vue";
  import FormTextarea from "@/components/FormTextarea.vue";
  import Layout from "@/layouts/Default.vue";
  import { index as requestsIndex } from "@/routes/development/requests";
  import { index as roadmapIndex } from "@/routes/development/roadmap";
  import { promote, reject } from "@/routes/development/tasks";
  import { store as requestInformation } from "@/routes/development/tasks/informationRequest";
  import { store as storeLink } from "@/routes/development/tasks/links";
  import { store as storeNote } from "@/routes/development/tasks/notes";
  import { update as updateProgress } from "@/routes/development/tasks/progress";
  import { store as startReview } from "@/routes/development/tasks/review";
  import { update as updateRoadmap } from "@/routes/development/tasks/roadmap";
  import { store as requestTesting } from "@/routes/development/tasks/testRuns";
  import { update as updateTestRun } from "@/routes/development/testRuns";
  import type { AppPageProps } from "@/types";
  import type { DevelopmentOption, DevelopmentTask } from "@/types/development";

  defineOptions({ layout: Layout });
  const props = defineProps<{
    task: DevelopmentTask;
    assignees: DevelopmentOption[];
    priorities: DevelopmentOption[];
    linkTypes: DevelopmentOption[];
    testers: DevelopmentOption[];
    plannedTaskCount: number;
  }>();
  const page = usePage<AppPageProps>();
  const toast = useToast();

  watch(
    () => page.props.flash.success,
    (value) => {
      if (value) toast.add({ title: value, color: "success" });
    },
    { immediate: true },
  );

  const canStartReview = computed(() => props.task.can.review && ["submitted", "needs_information"].includes(props.task.status));
  const canRequestInformation = computed(() => props.task.can.review && props.task.status === "submitted");
  const canDecide = computed(() => props.task.can.review && props.task.status === "under_review");
  const isRoadmapTask = computed(() => ["planned", "in_progress", "blocked", "changes_requested"].includes(props.task.status));
  const canEditRoadmapSummary = computed(() => props.task.can.manage_roadmap && isRoadmapTask.value);
  const pageContextLabel = computed(() => (isRoadmapTask.value ? "Roadmap task" : "Development request"));
  const taskStatusBadgeClass = computed(() => {
    if (props.task.status === "completed") return "bg-success-50 text-success-800 ring-success-300/80 dark:bg-success-950/30 dark:text-success-300 dark:ring-success-800/80";
    if (["rejected", "cancelled"].includes(props.task.status))
      return "bg-error-50 text-error-800 ring-error-300/80 dark:bg-error-950/30 dark:text-error-300 dark:ring-error-800/80";
    if (["needs_information", "blocked", "changes_requested"].includes(props.task.status)) {
      return "bg-warning-50 text-warning-800 ring-warning-300/80 dark:bg-warning-950/30 dark:text-warning-300 dark:ring-warning-800/80";
    }
    if (props.task.status === "under_review") return "bg-info-50 text-info-800 ring-info-300/80 dark:bg-info-950/30 dark:text-info-300 dark:ring-info-800/80";
    if (["planned", "in_progress"].includes(props.task.status))
      return "bg-primary-50 text-primary-800 ring-primary-300/80 dark:bg-primary-950/30 dark:text-primary-300 dark:ring-primary-800/80";

    return "bg-neutral-50 text-neutral-800 ring-neutral-300/80 dark:bg-neutral-900 dark:text-neutral-200 dark:ring-neutral-700/80";
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
  interface LifecycleStep {
    label: string;
    description: string;
  }
  const lifecycleSteps = computed<LifecycleStep[]>(() => {
    if (props.task.status === "rejected") {
      return [
        { label: "Submitted", description: "Request received." },
        { label: "Review", description: "Scope and outcome checked." },
        { label: "Rejected", description: "Request closed during review." },
      ];
    }

    if (props.task.status === "cancelled") {
      return [
        { label: "Submitted", description: "Request received." },
        { label: "Cancelled", description: "Work closed before completion." },
      ];
    }

    return [
      { label: "Submitted", description: "Request received." },
      { label: "Review", description: "Scope and outcome checked." },
      { label: "Planned", description: "Ordered in the roadmap queue." },
      { label: "Implementation", description: "Work is delivered." },
      { label: "User testing", description: "The requested outcome is verified." },
      { label: "Completed", description: "Accepted and archived." },
    ];
  });
  const currentLifecycleStepIndex = computed(() => {
    if (["rejected", "cancelled"].includes(props.task.status)) return lifecycleSteps.value.length - 1;

    return (
      {
        submitted: 0,
        needs_information: 1,
        under_review: 1,
        planned: 2,
        in_progress: 3,
        blocked: 3,
        changes_requested: 3,
        ready_for_testing: 4,
        completed: 5,
      }[props.task.status] ?? 0
    );
  });
  const progressAction = computed<{ color: "neutral" | "primary" | "warning"; label: string; status: string } | null>(() => {
    if (props.task.status === "planned") return { label: "Start work", status: "in_progress", color: "primary" };
    if (props.task.status === "in_progress") return { label: "Mark blocked", status: "blocked", color: "warning" };
    if (props.task.status === "blocked") return { label: "Resume work", status: "in_progress", color: "primary" };
    if (props.task.status === "changes_requested") return { label: "Resume implementation", status: "in_progress", color: "primary" };

    return null;
  });
  const showActionSidebar = computed(
    () =>
      canStartReview.value || canRequestInformation.value || canDecide.value || (props.task.can.update_progress && Boolean(progressAction.value)) || props.task.can.request_testing,
  );
  const noteAudiences = [
    { label: "Participants", value: "participants" },
    { label: "Development team only", value: "development_team" },
  ];
  const formatDateTime = (value: string): string => new Intl.DateTimeFormat("en-US", { dateStyle: "medium", timeStyle: "short" }).format(new Date(value));
  const isWebReference = (value: string | null): boolean => value?.startsWith("https://") === true || value?.startsWith("http://") === true;
  const testRunStatusBadgeClass = (status: string): string => {
    if (status === "accepted") return "bg-success-50 text-success-800 ring-success-300/80 dark:bg-success-950/30 dark:text-success-300 dark:ring-success-800/80";
    if (status === "changes_requested") return "bg-warning-50 text-warning-800 ring-warning-300/80 dark:bg-warning-950/30 dark:text-warning-300 dark:ring-warning-800/80";

    return "bg-info-50 text-info-800 ring-info-300/80 dark:bg-info-950/30 dark:text-info-300 dark:ring-info-800/80";
  };
</script>

<template>
  <UDashboardPanel id="development-task-detail">
    <template #header>
      <UDashboardNavbar :title="pageContextLabel">
        <template #leading><UDashboardSidebarCollapse /></template>
        <template #right>
          <UButton
            :label="isRoadmapTask ? 'Back to roadmap' : 'Back to my requests'"
            :to="isRoadmapTask ? roadmapIndex().url : requestsIndex().url"
            color="neutral"
            icon="i-ph-arrow-left"
            variant="ghost"
          />
        </template>
      </UDashboardNavbar>
    </template>

    <template #body>
      <UContainer class="grid gap-8 px-0 sm:px-0 lg:px-0">
        <UCard
          :ui="{
            root: 'rounded-2xl border border-secondary-200/80 bg-default dark:border-secondary-900/80 dark:bg-default',
            header: 'border-b border-secondary-200/70 bg-secondary-50/80 p-5 dark:border-secondary-900/70 dark:bg-secondary-950/10 sm:p-6',
            body: 'p-5 sm:p-6',
          }"
          data-testid="development-task-summary"
          variant="soft"
        >
          <template #header>
            <div class="grid min-w-0 gap-4">
              <div class="flex flex-wrap items-center gap-2">
                <UBadge :class="taskStatusBadgeClass" :label="task.status_label" class="text-sm sm:text-xs" color="neutral" variant="subtle" />
                <UBadge
                  :label="task.request_type_label"
                  class="bg-secondary-50 text-sm text-secondary-800 ring-secondary-300/80 dark:bg-secondary-950/30 dark:text-secondary-300 dark:ring-secondary-800/80 sm:text-xs"
                  color="neutral"
                  variant="subtle"
                />
                <UBadge
                  :label="task.affected_area_label"
                  class="bg-accent-50 text-sm text-accent-800 ring-accent-300/80 dark:bg-accent-950/30 dark:text-accent-300 dark:ring-accent-800/80 sm:text-xs"
                  color="neutral"
                  variant="subtle"
                />
              </div>
              <div class="grid gap-2">
                <p class="font-mono text-base text-secondary-800 dark:text-secondary-300 sm:text-sm">{{ pageContextLabel }}</p>
                <h1 class="max-w-[32ch] break-words text-2xl font-semibold tracking-tight text-balance text-highlighted sm:text-3xl">
                  {{ task.title }}
                </h1>
                <p class="max-w-[70ch] text-base/7 text-pretty text-muted sm:text-sm/6">
                  Submitted by {{ task.submitter }} on <span class="tabular-nums">{{ formatDateTime(task.submitted_at) }}</span
                  >.
                </p>
              </div>
            </div>
          </template>

          <div class="@container">
            <dl class="grid gap-3 @2xl:grid-cols-2 @5xl:grid-cols-4">
              <div class="grid gap-1 rounded-xl border border-info-200/80 bg-info-50/70 p-4 dark:border-info-900/80 dark:bg-info-950/10">
                <dt class="font-medium text-info-800 dark:text-info-300">Reviewer</dt>
                <dd class="text-base/7 text-muted sm:text-sm/6">{{ task.reviewer ?? "Unassigned" }}</dd>
              </div>
              <div class="grid gap-1 rounded-xl border border-secondary-200/80 bg-secondary-50/70 p-4 dark:border-secondary-900/80 dark:bg-secondary-950/10">
                <dt class="flex min-w-0 items-start justify-between gap-3 font-medium text-secondary-800 dark:text-secondary-300">
                  Assignee
                  <UButton
                    v-if="canEditRoadmapSummary"
                    :aria-pressed="activeRoadmapEditor === 'assignee'"
                    aria-label="Edit assignee"
                    class="-m-2 min-h-12 min-w-12 shrink-0 justify-center sm:m-0 sm:min-h-7 sm:min-w-7"
                    color="neutral"
                    icon="i-ph-pencil-simple"
                    size="xs"
                    type="button"
                    variant="ghost"
                    @click="editRoadmapSummary('assignee')"
                  />
                </dt>
                <dd v-if="activeRoadmapEditor !== 'assignee'" class="text-base/7 text-muted sm:text-sm/6">{{ task.assignee ?? "Unassigned" }}</dd>
                <dd v-else>
                  <Form
                    #default="{ errors, processing }"
                    :options="{ preserveScroll: true }"
                    :transform="transformRoadmapSummaryForm"
                    class="grid min-w-0 grid-cols-[minmax(0,1fr)_auto_auto] items-start gap-2"
                    v-bind="updateRoadmap.form(task.id)"
                    @success="closeRoadmapSummaryEditor"
                  >
                    <FormSelect
                      :default-value="task.assignee_id ?? unassignedAssigneeValue"
                      :error="errors.assignee_id"
                      :items="assigneeOptions"
                      aria-label="Assignee"
                      class="min-w-0"
                      name="assignee_id"
                    />
                    <UButton :loading="processing" color="neutral" label="Save" size="xs" type="submit" variant="soft" />
                    <UButton aria-label="Cancel assignee edit" color="neutral" icon="i-ph-x" size="xs" type="button" variant="ghost" @click="closeRoadmapSummaryEditor" />
                  </Form>
                </dd>
              </div>
              <div class="grid gap-1 rounded-xl border border-warning-200/80 bg-warning-50/70 p-4 dark:border-warning-900/80 dark:bg-warning-950/10">
                <dt class="flex min-w-0 items-start justify-between gap-3 font-medium text-warning-800 dark:text-warning-300">
                  Priority
                  <UButton
                    v-if="canEditRoadmapSummary"
                    :aria-pressed="activeRoadmapEditor === 'priority'"
                    aria-label="Edit priority"
                    class="-m-2 min-h-12 min-w-12 shrink-0 justify-center sm:m-0 sm:min-h-7 sm:min-w-7"
                    color="neutral"
                    icon="i-ph-pencil-simple"
                    size="xs"
                    type="button"
                    variant="ghost"
                    @click="editRoadmapSummary('priority')"
                  />
                </dt>
                <dd v-if="activeRoadmapEditor !== 'priority'" class="text-base/7 text-muted sm:text-sm/6">{{ task.priority_label }}</dd>
                <dd v-else>
                  <Form
                    #default="{ errors, processing }"
                    :options="{ preserveScroll: true }"
                    :transform="transformRoadmapSummaryForm"
                    class="grid min-w-0 grid-cols-[minmax(0,1fr)_auto_auto] items-start gap-2"
                    v-bind="updateRoadmap.form(task.id)"
                    @success="closeRoadmapSummaryEditor"
                  >
                    <FormSelect :default-value="task.priority" :error="errors.priority" :items="priorities" aria-label="Priority" class="min-w-0" name="priority" required />
                    <UButton :loading="processing" color="neutral" label="Save" size="xs" type="submit" variant="soft" />
                    <UButton aria-label="Cancel priority edit" color="neutral" icon="i-ph-x" size="xs" type="button" variant="ghost" @click="closeRoadmapSummaryEditor" />
                  </Form>
                </dd>
              </div>
              <div class="grid gap-1 rounded-xl border border-accent-200/80 bg-accent-50/70 p-4 dark:border-accent-900/80 dark:bg-accent-950/10">
                <dt class="flex min-w-0 items-start justify-between gap-3 font-medium text-accent-800 dark:text-accent-300">
                  Roadmap position
                  <UButton
                    v-if="canEditRoadmapSummary && task.status === 'planned'"
                    :aria-pressed="activeRoadmapEditor === 'roadmap_position'"
                    aria-label="Edit roadmap position"
                    class="-m-2 min-h-12 min-w-12 shrink-0 justify-center sm:m-0 sm:min-h-7 sm:min-w-7"
                    color="neutral"
                    icon="i-ph-pencil-simple"
                    size="xs"
                    type="button"
                    variant="ghost"
                    @click="editRoadmapSummary('roadmap_position')"
                  />
                </dt>
                <dd v-if="activeRoadmapEditor !== 'roadmap_position'" class="tabular-nums text-base/7 text-muted sm:text-sm/6">
                  {{ task.roadmap_position ? `#${task.roadmap_position}` : "Not scheduled" }}
                </dd>
                <dd v-else>
                  <Form
                    #default="{ errors, processing }"
                    :options="{ preserveScroll: true }"
                    :transform="transformRoadmapSummaryForm"
                    class="grid min-w-0 gap-3"
                    v-bind="updateRoadmap.form(task.id)"
                    @success="closeRoadmapSummaryEditor"
                  >
                    <div class="grid min-w-0 grid-cols-[minmax(0,1fr)_auto_auto] items-start gap-2">
                      <FormInput
                        :default-value="task.roadmap_position ?? plannedTaskCount"
                        :error="errors.roadmap_position"
                        :max="plannedTaskCount"
                        aria-label="Roadmap position"
                        class="min-w-0"
                        min="1"
                        name="roadmap_position"
                        required
                        type="number"
                      />
                      <UButton :loading="processing" color="neutral" label="Save" size="xs" type="submit" variant="soft" />
                      <UButton aria-label="Cancel roadmap position edit" color="neutral" icon="i-ph-x" size="xs" type="button" variant="ghost" @click="closeRoadmapSummaryEditor" />
                    </div>
                    <div class="flex flex-wrap gap-2">
                      <UButton
                        :disabled="processing || task.roadmap_position === 1"
                        color="neutral"
                        label="Move to start"
                        name="roadmap_destination"
                        size="xs"
                        type="submit"
                        value="start"
                        variant="outline"
                      />
                      <UButton
                        :disabled="processing || task.roadmap_position === plannedTaskCount"
                        color="neutral"
                        label="Move to end"
                        name="roadmap_destination"
                        size="xs"
                        type="submit"
                        value="end"
                        variant="outline"
                      />
                    </div>
                  </Form>
                </dd>
              </div>
            </dl>
          </div>
        </UCard>

        <div class="grid min-w-0 gap-10 xl:grid-cols-[minmax(0,1fr)_22rem] xl:items-start" data-testid="development-task-layout">
          <main class="grid min-w-0 gap-10">
            <UCard
              :ui="{
                root: 'rounded-2xl border border-primary-200/80 bg-default dark:border-primary-900/80 dark:bg-default',
                header: 'border-b border-primary-200/70 bg-primary-50/80 p-5 dark:border-primary-900/70 dark:bg-primary-950/10 sm:p-6',
                body: 'p-5 sm:p-6',
              }"
              aria-labelledby="task-brief-heading"
              data-testid="development-task-brief"
              variant="soft"
            >
              <template #header>
                <div class="grid gap-1">
                  <p class="font-mono text-base text-primary-800 dark:text-primary-300 sm:text-sm">01 / Request brief</p>
                  <h2 id="task-brief-heading" class="text-xl font-semibold text-balance text-highlighted">What needs to change</h2>
                  <p class="max-w-[72ch] text-base/7 text-pretty text-muted sm:text-sm/6">
                    The original request, intended result, and definition of done stay together as the source of truth.
                  </p>
                </div>
              </template>

              <article class="@container grid gap-5">
                <div class="grid gap-2 border-s-2 border-primary ps-4">
                  <h3 class="font-medium text-highlighted">Problem or request</h3>
                  <p class="whitespace-pre-wrap text-base/7 text-pretty text-toned sm:text-sm/6">{{ task.description }}</p>
                </div>
                <div class="grid gap-4 @2xl:grid-cols-2">
                  <div class="grid content-start gap-2 rounded-xl border border-secondary-200/80 bg-secondary-50/60 p-4 dark:border-secondary-900/80 dark:bg-secondary-950/10">
                    <h3 class="font-medium text-secondary-800 dark:text-secondary-300">Desired outcome</h3>
                    <p class="whitespace-pre-wrap text-base/7 text-pretty text-toned sm:text-sm/6">{{ task.desired_outcome }}</p>
                  </div>
                  <div class="grid content-start gap-2 rounded-xl border border-success-200/80 bg-success-50/60 p-4 dark:border-success-900/80 dark:bg-success-950/10">
                    <h3 class="font-medium text-success-800 dark:text-success-300">Acceptance criteria</h3>
                    <p class="whitespace-pre-wrap text-base/7 text-pretty text-toned sm:text-sm/6">{{ task.acceptance_criteria }}</p>
                  </div>
                </div>
              </article>
            </UCard>

            <UCard
              :ui="{
                root: 'rounded-2xl border border-info-200/80 bg-default dark:border-info-900/80 dark:bg-default',
                header: 'border-b border-info-200/70 bg-info-50/80 p-5 dark:border-info-900/70 dark:bg-info-950/10 sm:p-6',
                body: 'grid gap-5 p-5 sm:p-6',
              }"
              aria-labelledby="task-notes-heading"
              variant="soft"
            >
              <template #header>
                <div class="grid gap-1">
                  <p class="font-mono text-base text-info-800 dark:text-info-300 sm:text-sm">02 / Discussion</p>
                  <h2 id="task-notes-heading" class="text-xl font-semibold text-balance text-highlighted">Clarification and planning notes</h2>
                  <p class="max-w-[72ch] text-base/7 text-pretty text-muted sm:text-sm/6">
                    Participant notes are shared with the requester. Internal notes stay with the development team.
                  </p>
                </div>
              </template>

              <Form
                v-if="task.can.add_participant_note || task.can.add_internal_note"
                #default="{ errors, processing }"
                :options="{ preserveScroll: true }"
                class="grid gap-3 rounded-xl border border-info-200/70 bg-info-50/50 p-4 dark:border-info-900/70 dark:bg-info-950/10 sm:p-5"
                reset-on-success
                v-bind="storeNote.form(task.id)"
              >
                <FormTextarea :error="errors.body" label="Add a note" name="body" required />
                <FormSelect
                  v-if="task.can.add_internal_note"
                  :error="errors.audience"
                  :items="noteAudiences"
                  default-value="participants"
                  label="Audience"
                  name="audience"
                  required
                />
                <input v-else name="audience" type="hidden" value="participants" />
                <UButton :loading="processing" class="justify-self-end" color="neutral" icon="i-ph-plus" label="Add note" size="sm" type="submit" variant="soft" />
              </Form>

              <ol v-if="task.notes?.length" class="grid border-s border-info-300/70 ps-6 dark:border-info-800/70" role="list">
                <li v-for="note in task.notes" :key="note.id" class="relative grid gap-2 pb-6 last:pb-0">
                  <span aria-hidden="true" class="absolute -start-7 top-1 size-3 rounded-full border-2 border-info-600 bg-default dark:border-info-400" />
                  <div class="flex flex-wrap items-center gap-2">
                    <p class="text-base/7 text-muted sm:text-sm/6">
                      <span class="font-medium text-highlighted">{{ note.author }}</span>
                      <span aria-hidden="true"> · </span>
                      <time class="tabular-nums">{{ formatDateTime(note.created_at) }}</time>
                    </p>
                    <UBadge v-if="note.audience === 'development_team'" :label="note.audience_label" class="text-sm sm:text-xs" color="warning" size="sm" variant="subtle" />
                  </div>
                  <p class="whitespace-pre-wrap text-base/7 text-pretty text-toned sm:text-sm/6">{{ note.body }}</p>
                </li>
              </ol>
              <div v-else class="flex items-start gap-3 rounded-xl border border-dashed border-info-300/80 bg-info-50/40 p-4 dark:border-info-800/80 dark:bg-info-950/10">
                <UIcon class="size-4 shrink-0 text-muted" name="i-ph-chat-circle-dots" />
                <div class="grid gap-1">
                  <p class="font-medium text-highlighted">No notes yet</p>
                  <p class="text-base/7 text-pretty text-muted sm:text-sm/6">New context and decisions will appear here in chronological order.</p>
                </div>
              </div>
            </UCard>

            <UCard
              :ui="{
                root: 'rounded-2xl border border-accent-200/80 bg-default dark:border-accent-900/80 dark:bg-default',
                header: 'border-b border-accent-200/70 bg-accent-50/80 p-5 dark:border-accent-900/70 dark:bg-accent-950/10 sm:p-6',
                body: 'grid gap-5 p-5 sm:p-6',
              }"
              aria-labelledby="task-links-heading"
              variant="soft"
            >
              <template #header>
                <div class="grid gap-1">
                  <p class="font-mono text-base text-accent-800 dark:text-accent-300 sm:text-sm">03 / Delivery references</p>
                  <h2 id="task-links-heading" class="text-xl font-semibold text-balance text-highlighted">Task links</h2>
                  <p class="max-w-[72ch] text-base/7 text-pretty text-muted sm:text-sm/6">
                    Branches, pull requests, commits, deployments, and supporting references stay attached to this task.
                  </p>
                </div>
              </template>

              <Form
                v-if="task.can.attach_link"
                #default="{ errors, processing }"
                :options="{ preserveScroll: true }"
                class="grid gap-3 rounded-xl border border-accent-200/70 bg-accent-50/50 p-4 dark:border-accent-900/70 dark:bg-accent-950/10 sm:grid-cols-2 sm:p-5"
                reset-on-success
                v-bind="storeLink.form(task.id)"
              >
                <FormSelect :error="errors.type" :items="linkTypes" label="Link type" name="type" required />
                <FormInput :error="errors.label" label="Label" name="label" placeholder="Optional display label" />
                <FormInput :error="errors.url" class="sm:col-span-2" label="URL" name="url" placeholder="https://…" required type="url" />
                <UButton :loading="processing" class="justify-self-end sm:col-span-2" color="neutral" icon="i-ph-link" label="Attach link" size="sm" type="submit" variant="soft" />
              </Form>

              <ul
                v-if="task.links?.length"
                class="divide-y divide-accent-200/60 rounded-xl border border-accent-200/80 bg-default dark:divide-accent-900/60 dark:border-accent-900/80"
                role="list"
              >
                <li v-for="link in task.links" :key="link.id" class="flex items-start justify-between gap-4 p-4">
                  <a :href="link.url" class="group grid min-w-0 gap-1" rel="noreferrer" target="_blank">
                    <p class="flex items-center gap-2 font-medium text-highlighted underline decoration-primary/50 underline-offset-4">
                      {{ link.label || link.type_label }}
                      <UIcon class="size-4 shrink-0" name="i-ph-arrow-square-out" />
                      <span class="sr-only">(opens in a new tab)</span>
                    </p>
                    <p class="truncate text-base/7 text-muted sm:text-sm/6">{{ link.url }}</p>
                  </a>
                  <UBadge :label="link.type_label" class="shrink-0 text-sm sm:text-xs" color="neutral" size="sm" variant="subtle" />
                </li>
              </ul>
              <div v-else class="flex items-start gap-3 rounded-xl border border-dashed border-accent-300/80 bg-accent-50/40 p-4 dark:border-accent-800/80 dark:bg-accent-950/10">
                <UIcon class="size-4 shrink-0 text-muted" name="i-ph-link-break" />
                <div class="grid gap-1">
                  <p class="font-medium text-highlighted">No delivery references</p>
                  <p class="text-base/7 text-pretty text-muted sm:text-sm/6">Attach the first working branch, pull request, deployment, or supporting document.</p>
                </div>
              </div>
            </UCard>

            <UCard
              :ui="{
                root: 'rounded-2xl border border-warning-200/80 bg-default dark:border-warning-900/80 dark:bg-default',
                header: 'border-b border-warning-200/70 bg-warning-50/80 p-5 dark:border-warning-900/70 dark:bg-warning-950/10 sm:p-6',
                body: 'grid gap-5 p-5 sm:p-6',
              }"
              aria-labelledby="task-testing-heading"
              variant="soft"
            >
              <template #header>
                <div class="grid gap-1">
                  <p class="font-mono text-base text-warning-800 dark:text-warning-300 sm:text-sm">04 / Verification</p>
                  <h2 id="task-testing-heading" class="text-xl font-semibold text-balance text-highlighted">User-testing history</h2>
                  <p class="max-w-[72ch] text-base/7 text-pretty text-muted sm:text-sm/6">
                    Every round remains attached to the task, including feedback that caused another implementation pass.
                  </p>
                </div>
              </template>

              <ol v-if="task.test_runs?.length" class="grid gap-4" role="list">
                <li
                  v-for="testRun in task.test_runs"
                  :key="testRun.id"
                  class="grid gap-5 rounded-xl border border-warning-200/80 bg-warning-50/30 p-5 dark:border-warning-900/80 dark:bg-warning-950/10"
                >
                  <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="grid gap-1">
                      <p class="font-medium text-highlighted">{{ testRun.tester }}</p>
                      <p class="text-base/7 text-muted sm:text-sm/6">
                        Requested by {{ testRun.requested_by }} · <span class="tabular-nums">{{ formatDateTime(testRun.requested_at) }}</span>
                      </p>
                    </div>
                    <UBadge :class="testRunStatusBadgeClass(testRun.status)" :label="testRun.status_label" class="text-sm sm:text-xs" color="neutral" variant="subtle" />
                  </div>

                  <div class="@container">
                    <div class="grid gap-4 @2xl:grid-cols-2">
                      <div class="grid content-start gap-1">
                        <h3 class="font-medium text-highlighted">Instructions</h3>
                        <p class="whitespace-pre-wrap text-base/7 text-pretty text-muted sm:text-sm/6">{{ testRun.instructions }}</p>
                      </div>
                      <div v-if="testRun.build_reference" class="grid content-start gap-1">
                        <h3 class="font-medium text-highlighted">Build reference</h3>
                        <a v-if="isWebReference(testRun.build_reference)" :href="testRun.build_reference" class="break-all hover:underline" rel="noreferrer" target="_blank">
                          <p class="text-base/7 text-highlighted underline decoration-primary/50 underline-offset-4 sm:text-sm/6">
                            {{ testRun.build_reference }}
                          </p>
                          <span class="sr-only">(opens in a new tab)</span>
                        </a>
                        <p v-else class="break-all text-base/7 text-muted sm:text-sm/6">{{ testRun.build_reference }}</p>
                      </div>
                    </div>
                  </div>

                  <div v-if="testRun.feedback" class="grid gap-1 rounded-xl border border-info-200/80 bg-info-50/60 p-4 dark:border-info-900/80 dark:bg-info-950/10">
                    <h3 class="font-medium text-highlighted">Feedback</h3>
                    <p class="whitespace-pre-wrap text-base/7 text-pretty text-muted sm:text-sm/6">{{ testRun.feedback }}</p>
                  </div>

                  <Form v-if="testRun.can.record_result" #default="{ errors, processing }" class="grid gap-3 border-t border-default pt-5" v-bind="updateTestRun.form(testRun.id)">
                    <FormTextarea :error="errors.feedback" label="Testing feedback" name="feedback" />
                    <UAlert v-if="errors.status" :description="errors.status" color="error" variant="subtle" />
                    <div class="flex flex-wrap justify-end gap-3">
                      <UButton :disabled="processing" color="warning" label="Request changes" name="status" size="sm" type="submit" value="changes_requested" variant="soft" />
                      <UButton :loading="processing" color="success" label="Accept and complete" name="status" size="sm" type="submit" value="accepted" variant="soft" />
                    </div>
                  </Form>
                </li>
              </ol>
              <div
                v-else
                class="flex items-start gap-3 rounded-xl border border-dashed border-warning-300/80 bg-warning-50/40 p-4 dark:border-warning-800/80 dark:bg-warning-950/10"
              >
                <UIcon class="size-4 shrink-0 text-muted" name="i-ph-test-tube" />
                <div class="grid gap-1">
                  <p class="font-medium text-highlighted">No user-testing rounds</p>
                  <p class="text-base/7 text-pretty text-muted sm:text-sm/6">Testing instructions, builds, and outcomes will collect here when verification begins.</p>
                </div>
              </div>
            </UCard>
          </main>

          <aside class="min-w-0 xl:sticky xl:top-4 xl:self-start" data-testid="development-task-workflow">
            <UCard
              :ui="{
                root: 'rounded-2xl border border-secondary-200/80 bg-default dark:border-secondary-900/80 dark:bg-default',
                header: 'border-b border-secondary-200/70 bg-secondary-50/80 p-5 dark:border-secondary-900/70 dark:bg-secondary-950/10',
                body: 'p-0 sm:p-0',
              }"
              variant="soft"
            >
              <template #header>
                <div class="grid gap-1">
                  <p class="font-mono text-base text-secondary-800 dark:text-secondary-300 sm:text-sm">Workflow</p>
                  <h2 id="task-lifecycle-heading" class="text-xl font-semibold text-balance text-highlighted">Task lifecycle</h2>
                  <p class="text-base/7 text-pretty text-muted sm:text-sm/6">The current phase is highlighted below.</p>
                </div>
              </template>

              <section aria-labelledby="task-lifecycle-heading" class="p-5">
                <ol class="grid" role="list">
                  <li
                    v-for="(step, index) in lifecycleSteps"
                    :key="step.label"
                    :data-current="index === currentLifecycleStepIndex"
                    class="grid grid-cols-[1rem_minmax(0,1fr)] gap-3 pb-5 last:pb-0"
                  >
                    <div class="relative flex justify-center">
                      <span
                        v-if="index < lifecycleSteps.length - 1"
                        :class="index < currentLifecycleStepIndex ? 'bg-primary/50' : 'bg-neutral-200 dark:bg-neutral-800'"
                        aria-hidden="true"
                        class="absolute top-4 -bottom-5 w-px"
                      />
                      <span
                        :class="
                          index === currentLifecycleStepIndex
                            ? 'bg-primary ring-4 ring-primary/15'
                            : index < currentLifecycleStepIndex
                              ? 'bg-primary/50'
                              : 'bg-neutral-300 dark:bg-neutral-700'
                        "
                        aria-hidden="true"
                        class="relative size-3 shrink-0 rounded-full"
                      />
                    </div>
                    <div class="grid min-w-0 gap-1">
                      <p
                        :class="
                          index === currentLifecycleStepIndex
                            ? 'font-medium text-primary-800 dark:text-primary-300'
                            : index < currentLifecycleStepIndex
                              ? 'font-medium text-toned'
                              : 'text-muted'
                        "
                      >
                        {{ step.label }}
                      </p>
                      <p class="text-base/7 text-pretty text-muted sm:text-sm/6">{{ step.description }}</p>
                      <UBadge
                        v-if="index === currentLifecycleStepIndex"
                        :label="task.status_label"
                        class="w-fit bg-primary-50 text-sm text-primary-800 ring-primary-300/80 dark:bg-primary-950/30 dark:text-primary-300 dark:ring-primary-800/80 sm:text-xs"
                        color="neutral"
                        size="sm"
                        variant="subtle"
                      />
                    </div>
                  </li>
                </ol>
              </section>

              <div v-if="showActionSidebar" class="divide-y divide-default border-t border-default">
                <section v-if="canStartReview" aria-labelledby="start-review-heading" class="grid gap-4 border-s-2 border-primary bg-primary-50/40 p-5 dark:bg-primary-950/10">
                  <div class="grid gap-1">
                    <h3 id="start-review-heading" class="font-medium text-highlighted">Review this request</h3>
                    <p class="text-base/7 text-pretty text-muted sm:text-sm/6">Starting review assigns the request to you and unlocks the review decision.</p>
                  </div>
                  <Form #default="{ processing }" v-bind="startReview.form(task.id)">
                    <UButton :loading="processing" block label="Start review" size="sm" type="submit" />
                  </Form>
                </section>

                <section v-if="canDecide" aria-labelledby="review-decision-heading" class="grid gap-4 border-s-2 border-secondary bg-secondary-50/40 p-5 dark:bg-secondary-950/10">
                  <div class="grid gap-1">
                    <h3 id="review-decision-heading" class="font-medium text-highlighted">Review decision</h3>
                    <p class="text-base/7 text-pretty text-muted sm:text-sm/6">Promote approved work to the roadmap or record why it should stop here.</p>
                  </div>
                  <Form #default="{ processing }" v-bind="promote.form(task.id)">
                    <UButton :loading="processing" block icon="i-ph-map-pin" label="Add to roadmap" size="sm" type="submit" />
                  </Form>
                  <Form #default="{ errors, processing }" class="grid gap-3 border-t border-default pt-4" v-bind="reject.form(task.id)">
                    <FormTextarea :error="errors.body" label="Reason for rejection" name="body" required />
                    <UButton :loading="processing" class="justify-self-end" color="error" label="Reject request" size="sm" type="submit" variant="soft" />
                  </Form>
                </section>

                <section
                  v-if="task.can.update_progress && progressAction"
                  aria-labelledby="implementation-progress-heading"
                  class="grid gap-4 border-s-2 border-info bg-info-50/40 p-5 dark:bg-info-950/10"
                >
                  <div class="grid gap-1">
                    <h3 id="implementation-progress-heading" class="font-medium text-highlighted">Implementation progress</h3>
                    <p class="text-base/7 text-pretty text-muted sm:text-sm/6">Move the task to its next meaningful implementation state.</p>
                  </div>
                  <Form #default="{ errors, processing }" :options="{ preserveScroll: true }" class="grid gap-3" v-bind="updateProgress.form(task.id)">
                    <input :value="progressAction.status" name="status" type="hidden" />
                    <UAlert v-if="errors.assignee_id || errors.status" :description="errors.assignee_id || errors.status" color="error" variant="subtle" />
                    <UButton :color="progressAction.color" :label="progressAction.label" :loading="processing" block size="sm" type="submit" />
                  </Form>
                </section>

                <section
                  v-if="canRequestInformation"
                  aria-labelledby="request-information-heading"
                  class="grid gap-4 border-s-2 border-warning bg-warning-50/40 p-5 dark:bg-warning-950/10"
                >
                  <div class="grid gap-1">
                    <h3 id="request-information-heading" class="font-medium text-highlighted">Request more information</h3>
                    <p class="text-base/7 text-pretty text-muted sm:text-sm/6">Ask for the missing context before the request enters review.</p>
                  </div>
                  <Form #default="{ errors, processing }" class="grid gap-3" reset-on-success v-bind="requestInformation.form(task.id)">
                    <FormTextarea :error="errors.body" label="What needs clarification?" name="body" required />
                    <UButton :loading="processing" class="justify-self-end" color="neutral" label="Send request" size="sm" type="submit" variant="soft" />
                  </Form>
                </section>

                <section
                  v-if="task.can.request_testing"
                  aria-labelledby="request-testing-heading"
                  class="grid gap-4 border-s-2 border-success bg-success-50/40 p-5 dark:bg-success-950/10"
                >
                  <div class="grid gap-1">
                    <h3 id="request-testing-heading" class="font-medium text-highlighted">Request user testing</h3>
                    <p class="text-base/7 text-pretty text-muted sm:text-sm/6">Assign a tester and describe the exact build and behavior they should verify.</p>
                  </div>
                  <Form #default="{ errors, processing }" :options="{ preserveScroll: true }" class="grid gap-3" v-bind="requestTesting.form(task.id)">
                    <FormSelect :default-value="task.assignee_id ?? ''" :error="errors.tester_id" :items="testers" label="Tester" name="tester_id" required />
                    <FormTextarea :error="errors.instructions" label="Testing instructions" name="instructions" required />
                    <FormInput
                      :error="errors.build_reference"
                      label="Build or deployment reference"
                      name="build_reference"
                      placeholder="Deployment URL, commit, or build identifier"
                    />
                    <UAlert v-if="errors.status" :description="errors.status" color="error" variant="subtle" />
                    <UButton :loading="processing" class="justify-self-end" color="neutral" icon="i-ph-test-tube" label="Send to testing" size="sm" type="submit" variant="soft" />
                  </Form>
                </section>
              </div>
            </UCard>
          </aside>
        </div>
      </UContainer>
    </template>
  </UDashboardPanel>
</template>
