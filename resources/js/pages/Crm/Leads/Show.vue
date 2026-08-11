<script lang="ts" setup>
import CrmDetailLayout from "@/components/Crm/CrmDetailLayout.vue";
import { edit, index } from "@/routes/crm/leads";
import LeadFollowUpsSection from "@/sections/Crm/Leads/LeadFollowUpsSection.vue";
import LeadNotesSection from "@/sections/Crm/Leads/LeadNotesSection.vue";
import LeadOverviewSection from "@/sections/Crm/Leads/LeadOverviewSection.vue";
import LeadTimelineSection from "@/sections/Crm/Leads/LeadTimelineSection.vue";
import type { Lead } from "@/types/crm";

defineProps<{ lead: Lead; canManage: boolean }>();
</script>

<template>
  <CrmDetailLayout :document-title="lead.contact?.name ?? 'Lead details'" :edit-url="canManage ? edit(lead.id).url : undefined" :index-url="index().url" :navbar-title="lead.contact?.name ?? 'Lead details'" index-label="All leads" panel-id="crm-lead-detail">
    <template #summary>
      <LeadOverviewSection :can-manage="canManage" :lead="lead" />
    </template>

    <LeadTimelineSection :activities="lead.activities" />

    <template #aside>
      <div class="grid content-start gap-8">
        <LeadFollowUpsSection :can-manage="canManage" :lead="lead" />
        <LeadNotesSection :can-manage="canManage" :lead="lead" />
      </div>
    </template>
  </CrmDetailLayout>
</template>
