<script lang="ts" setup>
import { usePage } from "@inertiajs/vue3";
import Layout from "@/layouts/Default.vue";
import { edit, index } from "@/routes/crm/leads";
import LeadFollowUpsSection from "@/sections/crm/leads/LeadFollowUpsSection.vue";
import LeadNotesSection from "@/sections/crm/leads/LeadNotesSection.vue";
import LeadOverviewSection from "@/sections/crm/leads/LeadOverviewSection.vue";
import LeadTimelineSection from "@/sections/crm/leads/LeadTimelineSection.vue";
import type { AppPageProps } from "@/types";
import type { Lead } from "@/types/crm";

defineOptions({ layout: Layout });
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
  <UDashboardPanel id="crm-lead-detail">
    <template #header>
      <UDashboardNavbar :title="lead.contact?.name ?? 'Lead details'">
        <template #leading><UDashboardSidebarCollapse /></template>
        <template #right>
          <UButton :to="index().url" color="neutral" label="All leads" variant="ghost" />
          <UButton v-if="canManage" :to="edit(lead.id).url" color="neutral" label="Edit" variant="outline" />
        </template>
      </UDashboardNavbar>
    </template>

    <template #body>
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
    </template>
  </UDashboardPanel>
</template>
