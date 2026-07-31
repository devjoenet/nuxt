<script lang="ts" setup>
import { usePage } from "@inertiajs/vue3";
import Layout from "@/layouts/Default.vue";
import { index as requestsIndex } from "@/routes/development/requests";
import { index as roadmapIndex } from "@/routes/development/roadmap";
import DevelopmentTaskBriefSection from "@/sections/development/tasks/DevelopmentTaskBriefSection.vue";
import DevelopmentTaskDiscussionSection from "@/sections/development/tasks/DevelopmentTaskDiscussionSection.vue";
import DevelopmentTaskLinksSection from "@/sections/development/tasks/DevelopmentTaskLinksSection.vue";
import DevelopmentTaskSummarySection from "@/sections/development/tasks/DevelopmentTaskSummarySection.vue";
import DevelopmentTaskTestingSection from "@/sections/development/tasks/DevelopmentTaskTestingSection.vue";
import DevelopmentTaskWorkflowSection from "@/sections/development/tasks/DevelopmentTaskWorkflowSection.vue";
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

const isRoadmapTask = computed(() => ["planned", "in_progress", "blocked", "changes_requested"].includes(props.task.status));
const pageContextLabel = computed(() => (isRoadmapTask.value ? "Roadmap task" : "Development request"));
</script>

<template>
  <UDashboardPanel id="development-task-detail">
    <template #header>
      <UDashboardNavbar :title="pageContextLabel">
        <template #leading><UDashboardSidebarCollapse /></template>
        <template #right>
          <UButton :label="isRoadmapTask ? 'Back to roadmap' : 'Back to my requests'" :to="isRoadmapTask ? roadmapIndex().url : requestsIndex().url" color="neutral" icon="i-ph-arrow-left" variant="ghost" />
        </template>
      </UDashboardNavbar>
    </template>

    <template #body>
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

          <UContainer class="relative grid gap-8 px-4 py-8 sm:px-6 lg:gap-10 lg:px-8 lg:py-10">
            <DevelopmentTaskSummarySection :assignees="assignees" :page-context-label="pageContextLabel" :planned-task-count="plannedTaskCount" :priorities="priorities" :task="task" />

            <div class="grid min-w-0 gap-8 xl:grid-cols-[minmax(0,1fr)_22rem] xl:items-start" data-testid="development-task-layout">
              <main class="grid min-w-0 gap-8">
                <DevelopmentTaskBriefSection :task="task" />
                <DevelopmentTaskDiscussionSection :task="task" />
                <DevelopmentTaskLinksSection :link-types="linkTypes" :task="task" />
                <DevelopmentTaskTestingSection :task="task" />
              </main>

              <DevelopmentTaskWorkflowSection :task="task" :testers="testers" />
            </div>
          </UContainer>
        </div>
      </UTheme>
    </template>
  </UDashboardPanel>
</template>
