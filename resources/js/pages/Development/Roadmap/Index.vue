<script lang="ts" setup>
import { Head } from "@inertiajs/vue3";
import { index as userTestingIndex } from "@/routes/development/userTesting";
import RoadmapColumnSection from "@/sections/development/roadmap/RoadmapColumnSection.vue";
import type { RoadmapColumn } from "@/sections/development/roadmap/types";
import type { DevelopmentRoadmapTasks } from "@/types/development";

const auth = useAuth();
const props = defineProps<{ tasks: DevelopmentRoadmapTasks }>();

const columns: RoadmapColumn[] = [
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
        <RoadmapColumnSection v-for="column in columns" :key="column.key" :column="column" :tasks="tasks[column.key]" />
      </div>
    </template>
  </UDashboardPanel>
</template>
