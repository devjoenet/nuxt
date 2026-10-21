<script setup lang="ts">
import { workspaceDashboardWidgets } from "@/pages/Dashboard/workspaceContributions";
import { usePage } from "@inertiajs/vue3";
const page = usePage();
defineProps<{ dashboardWidgets?: string[] }>();
</script>

<template>
  <WorkspacePage id="workspace-dashboard" description="A practical view of the work that needs attention." title="Workspace">
    <component :is="workspaceDashboardWidgets[widget]" v-for="widget in dashboardWidgets ?? []" :key="widget" v-bind="page.props" />
    <AppPageCard v-if="!dashboardWidgets?.length" description="Your account is active. Workspace tools appear when available for your role." icon="i-ph-shield-check" title="Workspace access is being prepared" variant="info" />
  </WorkspacePage>
</template>
