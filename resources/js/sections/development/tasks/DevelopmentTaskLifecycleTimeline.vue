<script setup lang="ts">
  import { cn } from "@/lib/utils";
  import type { DevelopmentTask } from "@/types/development";

  const props = defineProps<{
    task: DevelopmentTask;
    class?: string;
  }>();

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
</script>

<template>
  <section :class="cn('p-5 sm:p-6', props.class)" aria-labelledby="task-lifecycle-heading">
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
            :class="index < currentLifecycleStepIndex ? 'bg-primary/50' : 'bg-accented'"
            aria-hidden="true"
            class="absolute top-4 -bottom-5 w-px"
          />
          <span
            :class="index === currentLifecycleStepIndex ? 'bg-primary ring-4 ring-primary/15' : index < currentLifecycleStepIndex ? 'bg-primary/50' : 'bg-accented'"
            aria-hidden="true"
            class="relative size-3 shrink-0 rounded-full"
          />
        </div>
        <div class="grid min-w-0 gap-1">
          <p :class="index === currentLifecycleStepIndex ? 'font-medium text-primary' : index < currentLifecycleStepIndex ? 'font-medium text-highlighted' : 'text-muted'">
            {{ step.label }}
          </p>
          <p class="text-base/7 text-pretty text-muted sm:text-sm/6">{{ step.description }}</p>
          <UBadge v-if="index === currentLifecycleStepIndex" :label="task.status_label" class="w-fit" color="primary" variant="subtle" />
        </div>
      </li>
    </ol>
  </section>
</template>
