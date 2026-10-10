<script setup lang="ts">
import { router } from "@inertiajs/vue3";
import { RecordHistoryTable } from "@/components/RecordHistoryTable";
import type { HistoryPage } from "@/components/RecordHistoryTable";
import { history, show } from "@/routes/crm/leads";

const props = defineProps<{ lead: { id: string }; activities: HistoryPage }>();
const visitPage = (page: number): void => router.get(history(props.lead.id).url, { page }, { preserveScroll: true });
</script>

<template>
  <WorkspacePage id="lead-history" title="Lead history" description="Recorded changes to this lead, its notes, and its follow-ups. Older events include only the details captured at the time.">
    <template #toolbar><UButton :to="show(lead.id).url" color="neutral" icon="i-ph-arrow-left" label="Back to lead" variant="outline" /></template>
    <AppPageCard :style="'outline'" :ui="{ container: 'p-0 sm:p-0' }">
      <RecordHistoryTable :activities="activities" @page="visitPage" />
    </AppPageCard>
  </WorkspacePage>
</template>
