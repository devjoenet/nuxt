<script setup lang="ts">
import { router } from "@inertiajs/vue3";
import { RecordHistoryTable } from "@/components/RecordHistoryTable";
import type { HistoryPage } from "@/components/RecordHistoryTable";

const props = defineProps<{ label: string; backUrl: string; historyUrl: string; activities: HistoryPage }>();
const visitPage = (page: number): void => router.get(props.historyUrl, { page }, { preserveScroll: true });
</script>

<template>
  <WorkspacePage id="record-history" :title="`${label} history`" description="Recorded changes to this record. Older events include only the details captured at the time.">
    <template #toolbar><UButton :to="backUrl" color="neutral" icon="i-ph-arrow-left" :label="`Back to ${label.toLowerCase()}`" variant="outline" /></template>
    <AppPageCard :style="'outline'" :ui="{ container: 'p-0 sm:p-0' }">
      <RecordHistoryTable :activities="activities" @page="visitPage" />
    </AppPageCard>
  </WorkspacePage>
</template>
