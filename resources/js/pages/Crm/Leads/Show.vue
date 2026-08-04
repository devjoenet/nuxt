<script lang="ts" setup>
import { usePage } from "@inertiajs/vue3";
import { edit, index } from "@/routes/crm/leads";
import LeadFollowUpsSection from "@/sections/Crm/Leads/LeadFollowUpsSection.vue";
import LeadNotesSection from "@/sections/Crm/Leads/LeadNotesSection.vue";
import LeadOverviewSection from "@/sections/Crm/Leads/LeadOverviewSection.vue";
import LeadTimelineSection from "@/sections/Crm/Leads/LeadTimelineSection.vue";
import type { AppPageProps } from "@/types";
import type { Lead } from "@/types/crm";

defineProps<{ lead: Lead; canManage: boolean }>();
const page = usePage<AppPageProps>();
const toast = useToast();

watch(
  () => page.props.flash.success,
  (value) => {
    if (value) toast.add({ title: value, color: "success" });
  },
  { immediate: true },
);
</script>

<template>
  <WorkspacePage id="crm-lead-detail" :title="lead.contact?.name ?? 'Lead details'">
    <template #actions>
      <UButton :to="index().url" color="neutral" label="All leads" variant="ghost" />
      <UButton v-if="canManage" :to="edit(lead.id).url" color="neutral" label="Edit" variant="outline" />
    </template>

    <div class="grid gap-8 xl:grid-cols-[2fr_1fr]">
      <div class="grid gap-8">
        <LeadOverviewSection :can-manage="canManage" :lead="lead" />
        <LeadTimelineSection :activities="lead.activities" />
      </div>

      <aside class="grid content-start gap-8">
        <LeadFollowUpsSection :can-manage="canManage" :lead="lead" />
        <LeadNotesSection :can-manage="canManage" :lead="lead" />
      </aside>
    </div>
  </WorkspacePage>
</template>
