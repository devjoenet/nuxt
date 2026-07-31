<script setup lang="ts">
  import DevelopmentTaskSectionCard from "@/sections/development/tasks/DevelopmentTaskSectionCard.vue";
  import type { DevelopmentTask } from "@/types/development";

  defineProps<{
    task: DevelopmentTask;
    class?: string;
  }>();
</script>

<template>
  <DevelopmentTaskSectionCard
    :class="$props.class"
    :description="task.description"
    eyebrow="01 / Request brief"
    heading-id="task-brief-heading"
    title="Summary"
    tone="primary"
    data-testid="development-task-brief"
  >
    <article class="@container grid gap-6">
      <div class="grid gap-4 @2xl:grid-cols-2">
        <UPageCard
          :description="task.desired_outcome"
          title="Desired outcome"
          :ui="{
            root: 'rounded-lg',
            container: 'p-4 sm:p-5',
            description: 'whitespace-pre-wrap text-base/7 sm:text-sm/6',
          }"
          variant="soft"
        />
        <UPageCard
          :description="task.acceptance_criteria"
          title="Acceptance criteria"
          :ui="{
            root: 'rounded-lg',
            container: 'p-4 sm:p-5',
            description: 'whitespace-pre-wrap text-base/7 sm:text-sm/6',
          }"
          variant="soft"
        />
      </div>
    </article>
  </DevelopmentTaskSectionCard>
</template>
