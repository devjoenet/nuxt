<script setup lang="ts">
import { Card } from "@/components/Ui/Card";
import { cn } from "@/lib/utils";
import DevelopmentTaskLifecycleTimeline from "@/pages/Development/Tasks/Components/DevelopmentTaskLifecycleTimeline.vue";
import DevelopmentTaskWorkflowActions from "@/pages/Development/Tasks/Components/DevelopmentTaskWorkflowActions.vue";
import type { DevelopmentOption, DevelopmentTask } from "@/types/development";

const props = defineProps<{
  task: DevelopmentTask;
  testers: DevelopmentOption[];
  class?: string;
}>();
</script>

<template>
  <aside :class="cn('min-w-0 xl:sticky xl:top-4 xl:self-start', props.class)" data-testid="development-task-workflow">
    <Card padding="none" tone="secondary" variant="outline">
      <header class="grid gap-3 border-b border-default p-5 sm:p-6">
        <UBadge class="w-fit font-mono tracking-wide text-highlighted uppercase" color="secondary" label="Workflow" variant="soft" />
        <div class="grid gap-2">
          <h2 id="task-lifecycle-heading" class="text-2xl font-semibold tracking-tight text-balance text-highlighted">Task lifecycle</h2>
          <p class="text-base/7 text-pretty text-muted sm:text-sm/6">The current phase is highlighted below.</p>
        </div>
      </header>

      <DevelopmentTaskLifecycleTimeline :task="task" />
      <DevelopmentTaskWorkflowActions :task="task" :testers="testers" />
    </Card>
  </aside>
</template>
