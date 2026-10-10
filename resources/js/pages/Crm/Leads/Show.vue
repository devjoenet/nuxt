<script lang="ts" setup>
import { CrmDetailLayout } from "@/components/CrmDetailLayout";
import { edit, index } from "@/routes/crm/leads";
import LeadFollowUpsSection from "@/pages/Crm/Leads/Components/LeadFollowUpsSection.vue";
import LeadNotesSection from "@/pages/Crm/Leads/Components/LeadNotesSection.vue";
import LeadOverviewSection from "@/pages/Crm/Leads/Components/LeadOverviewSection.vue";
import LeadNoteHistorySection from "@/pages/Crm/Leads/Components/LeadNoteHistorySection.vue";
import type { Lead } from "@/types/crm";

defineProps<{ lead: Lead; canManage: boolean; canViewHistory: boolean }>();
</script>

<template>
  <CrmDetailLayout :document-title="lead.contact?.name ?? 'Lead details'" :edit-url="canManage ? edit(lead.id).url : undefined" :index-url="index().url" :navbar-title="lead.contact?.name ?? 'Lead details'" index-label="All leads" panel-id="crm-lead-detail">
    <template #summary>
      <LeadOverviewSection :can-manage="canManage" :can-view-history="canViewHistory" :lead="lead" />
    </template>

    <LeadNoteHistorySection :notes="lead.notes" />

    <template #aside>
      <div class="grid content-start gap-8">
        <LeadFollowUpsSection :can-manage="canManage" :lead="lead" />
        <LeadNotesSection v-if="canManage" :can-manage="canManage" :lead="lead" />
      </div>
    </template>
  </CrmDetailLayout>
</template>
