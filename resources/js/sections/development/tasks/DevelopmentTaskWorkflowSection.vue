<script setup lang="ts">
import { cn } from "@/lib/utils";
import DevelopmentTaskLifecycleTimeline from "@/sections/development/tasks/DevelopmentTaskLifecycleTimeline.vue";
import DevelopmentTaskWorkflowActions from "@/sections/development/tasks/DevelopmentTaskWorkflowActions.vue";
import type { DevelopmentOption, DevelopmentTask } from "@/types/development";

const props = defineProps<{
  task: DevelopmentTask;
  testers: DevelopmentOption[];
  class?: string;
}>();
</script>

<template>
  <aside :class="cn('min-w-0 xl:sticky xl:top-4 xl:self-start', props.class)" data-testid="development-task-workflow">
    <UCard
      class="bg-default/95 shadow-lg dark:shadow-none"
      :ui="{
        root: 'relative overflow-hidden rounded-xl ring-default',
        header: 'border-b border-default p-5 sm:p-6',
        body: 'p-0 sm:p-0',
      }"
      variant="subtle"
    >
      <template #header>
        <div class="grid gap-3">
          <UBadge class="w-fit font-mono tracking-wide text-highlighted uppercase" color="secondary" label="Workflow" variant="soft" />
          <div class="grid gap-2">
            <h2 id="task-lifecycle-heading" class="text-2xl font-semibold tracking-tight text-balance text-highlighted">Task lifecycle</h2>
            <p class="text-base/7 text-pretty text-muted sm:text-sm/6">The current phase is highlighted below.</p>
          </div>
        </div>
      </template>

      <DevelopmentTaskLifecycleTimeline :task="task" />
      <DevelopmentTaskWorkflowActions :task="task" :testers="testers" />
    </UCard>
  </aside>
</template>
