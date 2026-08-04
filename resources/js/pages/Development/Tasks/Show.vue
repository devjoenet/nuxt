<script lang="ts" setup>
import { usePage } from "@inertiajs/vue3";
import { index as requestsIndex } from "@/routes/development/requests";
import { index as roadmapIndex } from "@/routes/development/roadmap";
import DevelopmentTaskBriefSection from "@/sections/Development/Tasks/DevelopmentTaskBriefSection.vue";
import DevelopmentTaskDiscussionSection from "@/sections/Development/Tasks/DevelopmentTaskDiscussionSection.vue";
import DevelopmentTaskLinksSection from "@/sections/Development/Tasks/DevelopmentTaskLinksSection.vue";
import DevelopmentTaskSummarySection from "@/sections/Development/Tasks/DevelopmentTaskSummarySection.vue";
import DevelopmentTaskTestingSection from "@/sections/Development/Tasks/DevelopmentTaskTestingSection.vue";
import DevelopmentTaskWorkflowSection from "@/sections/Development/Tasks/DevelopmentTaskWorkflowSection.vue";
import type { AppPageProps } from "@/types";
import type { DevelopmentOption, DevelopmentTask } from "@/types/development";

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

const isRoadmapTask = computed(() => ["planned", "in_progress", "blocked", "changes_requested"].includes(props.task.status));
const pageContextLabel = computed(() => (isRoadmapTask.value ? "Roadmap task" : "Development request"));
</script>

<template>
  <WorkspacePage id="development-task-detail" :document-title="task.title" :title="pageContextLabel">
    <template #actions>
      <UButton :label="isRoadmapTask ? 'Back to roadmap' : 'Back to my requests'" :to="isRoadmapTask ? roadmapIndex().url : requestsIndex().url" color="neutral" icon="i-ph-arrow-left" variant="ghost" />
    </template>

    <UTheme
      :props="{
        badge: { size: 'md' },
        button: { size: 'sm' },
      }"
      :ui="{
        badge: { base: 'rounded-md' },
        button: { base: 'rounded-md' },
        card: {
          root: 'rounded-xl bg-elevated ring-accented shadow-sm shadow-default/5 dark:shadow-none dark:inset-ring dark:inset-ring-accented',
        },
      }"
    >
      <div class="relative min-h-full bg-default" data-testid="development-task-scroll-content">
        <div aria-hidden="true" class="pointer-events-none absolute inset-x-0 top-0 h-80 bg-linear-to-b from-primary/10 via-secondary/5 to-transparent dark:from-primary/5 dark:via-secondary/5" />

        <UContainer :key="task.id" class="relative grid gap-8 px-4 py-8 sm:px-6 lg:gap-10 lg:px-8 lg:py-10">
          <DevelopmentTaskSummarySection :assignees="assignees" :page-context-label="pageContextLabel" :planned-task-count="plannedTaskCount" :priorities="priorities" :task="task" />

          <div class="grid min-w-0 gap-8 xl:grid-cols-[minmax(0,1fr)_22rem] xl:items-start" data-testid="development-task-layout">
            <div class="grid min-w-0 gap-8">
              <DevelopmentTaskBriefSection :task="task" />
              <DevelopmentTaskDiscussionSection :task="task" />
              <DevelopmentTaskLinksSection :link-types="linkTypes" :task="task" />
              <DevelopmentTaskTestingSection :task="task" />
            </div>

            <DevelopmentTaskWorkflowSection :task="task" :testers="testers" />
          </div>
        </UContainer>
      </div>
    </UTheme>
  </WorkspacePage>
</template>
