<script setup lang="ts">
import { cn } from "@/lib/utils";
import { show } from "@/routes/development/tasks";
import type { RoadmapColumn } from "@/sections/development/roadmap/types";
import type { DevelopmentTask } from "@/types/development";

const props = defineProps<{
  column: RoadmapColumn;
  tasks: DevelopmentTask[];
  class?: string;
}>();

const priorityColor = (task: DevelopmentTask): "error" | "neutral" | "warning" => {
  if (task.priority === "high") return "error";
  if (task.priority === "low") return "neutral";

  return "warning";
};
</script>

<template>
  <section :aria-labelledby="`${column.key}-heading`" :class="cn('grid min-w-0 gap-3', props.class)">
    <header class="flex items-start justify-between gap-3 border-b border-default pb-3">
      <div>
        <h2 :id="`${column.key}-heading`" class="flex items-center gap-2 font-semibold text-highlighted">
          <UIcon :name="column.icon" class="size-5 text-primary" />
          {{ column.title }}
        </h2>
        <p class="mt-1 text-sm text-muted">{{ column.description }}</p>
      </div>
      <UBadge :label="String(tasks.length)" color="neutral" variant="subtle" />
    </header>

    <ul v-if="tasks.length" class="grid gap-3" role="list">
      <li v-for="task in tasks" :key="task.id">
        <UPageCard :to="show(task.id).url" variant="subtle">
          <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
              <p v-if="column.key === 'planned' && task.roadmap_position" class="text-xs font-bold tracking-[0.14em] text-primary uppercase">Planned {{ task.roadmap_position }}</p>
              <h3 class="mt-1 font-semibold text-highlighted">{{ task.title }}</h3>
            </div>
            <UBadge :color="priorityColor(task)" :label="task.priority_label" size="sm" variant="subtle" />
          </div>
          <p class="mt-3 line-clamp-2 text-sm leading-6 text-muted">{{ task.desired_outcome }}</p>
          <dl class="mt-4 grid grid-cols-2 gap-3 border-t border-default pt-3 text-sm">
            <div>
              <dt class="text-muted">Assignee</dt>
              <dd class="truncate font-medium text-highlighted">{{ task.assignee ?? "Unassigned" }}</dd>
            </div>
            <div>
              <dt class="text-muted">Area</dt>
              <dd class="truncate font-medium text-highlighted">{{ task.affected_area_label }}</dd>
            </div>
          </dl>
        </UPageCard>
      </li>
    </ul>

    <UEmpty v-else :description="column.empty" :icon="column.icon" :title="`No ${column.title.toLowerCase()} tasks`" />
  </section>
</template>
