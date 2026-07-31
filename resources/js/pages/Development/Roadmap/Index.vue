<script lang="ts" setup>
  import { Head } from "@inertiajs/vue3";
  import Layout from "@/layouts/Default.vue";
  import { show } from "@/routes/development/tasks";
  import { index as userTestingIndex } from "@/routes/development/userTesting";
  import type { DevelopmentRoadmapTasks, DevelopmentTask } from "@/types/development";

  defineOptions({ layout: Layout });

  const auth = useAuth();
  const props = defineProps<{ tasks: DevelopmentRoadmapTasks }>();

  const columns: Array<{
    description: string;
    empty: string;
    icon: string;
    key: keyof DevelopmentRoadmapTasks;
    title: string;
  }> = [
    {
      key: "planned",
      title: "Planned",
      description: "Prioritized work waiting to start.",
      empty: "No work is currently planned.",
      icon: "i-ph-map-pin",
    },
    {
      key: "in_progress",
      title: "In progress",
      description: "Assigned work under active implementation.",
      empty: "No work is currently in progress.",
      icon: "i-ph-hammer",
    },
    {
      key: "blocked",
      title: "Blocked",
      description: "Work waiting on a decision or dependency.",
      empty: "No work is currently blocked.",
      icon: "i-ph-warning",
    },
  ];

  const totalTasks = computed(() => Object.values(props.tasks).reduce((total, tasks) => total + tasks.length, 0));

  const priorityColor = (task: DevelopmentTask): "error" | "neutral" | "warning" => {
    if (task.priority === "high") return "error";
    if (task.priority === "low") return "neutral";

    return "warning";
  };
</script>

<template>
  <Head title="Development roadmap" />

  <UDashboardPanel id="development-roadmap">
    <template #header>
      <UDashboardNavbar description="Planned, active, and blocked work from the Development workspace." title="Development roadmap">
        <template #leading><UDashboardSidebarCollapse /></template>
        <template #right>
          <UButton v-if="auth.user?.can?.['development.user_testing.manage']" :to="userTestingIndex().url" color="neutral" label="User testing" variant="ghost" />
        </template>
      </UDashboardNavbar>
    </template>

    <template #body>
      <UEmpty v-if="totalTasks === 0" description="Promoted requests will appear here as database-backed roadmap tasks." icon="i-ph-map-trifold" title="The roadmap is clear" />

      <div v-else class="grid items-start gap-5 xl:grid-cols-3">
        <section v-for="column in columns" :key="column.key" :aria-labelledby="`${column.key}-heading`" class="grid min-w-0 gap-3">
          <header class="flex items-start justify-between gap-3 border-b border-default pb-3">
            <div>
              <h2 :id="`${column.key}-heading`" class="flex items-center gap-2 font-semibold text-highlighted">
                <UIcon :name="column.icon" class="size-5 text-primary" />
                {{ column.title }}
              </h2>
              <p class="mt-1 text-sm text-muted">{{ column.description }}</p>
            </div>
            <UBadge :label="String(tasks[column.key].length)" color="neutral" variant="subtle" />
          </header>

          <ul v-if="tasks[column.key].length" class="grid gap-3" role="list">
            <li v-for="task in tasks[column.key]" :key="task.id">
              <UPageCard :to="show(task.id).url" variant="subtle">
                <div class="flex items-start justify-between gap-3">
                  <div class="min-w-0">
                    <p v-if="column.key === 'planned' && task.roadmap_position" class="text-xs font-bold tracking-[0.14em] text-primary uppercase">
                      Planned {{ task.roadmap_position }}
                    </p>
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
      </div>
    </template>
  </UDashboardPanel>
</template>
