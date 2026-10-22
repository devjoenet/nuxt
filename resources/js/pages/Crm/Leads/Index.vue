<script lang="ts" setup>
import { ownerOptions as mapOwnerOptions, type OwnerOption } from "@southeastcode/crm/public";
import { IndexEmptyState } from "@/components/IndexEmptyState";
import { IndexPagination } from "@/components/IndexPagination";
import { IndexTableFrame } from "@/components/IndexTableFrame";
import { FormInput } from "@/components/Ui/FormInput";
import { FormSelect } from "@/components/Ui/FormSelect";
import { useInertiaFilters } from "@/composables/useInertiaFilters";
import { create, index } from "@/routes/crm/leads";
import { createLeadIndexColumns } from "@/pages/Crm/Leads/Components/leadIndexColumns";
import type { Lead, Option, Paginated } from "@southeastcode/crm/public";

const props = defineProps<{
  leads: Paginated<Lead>;
  filters: Record<string, string | number>;
  statuses: Option[];
  sources: Option[];
  owners: OwnerOption[];
  canManage: boolean;
  canAssign: boolean;
}>();
const ownerOptions = computed<Option[]>(() => mapOwnerOptions(props.owners, true));
const { filters, hasActiveFilters, isLoading, reset, visitPage } = useInertiaFilters({
  url: () => index().url,
  initialFilters: () => ({
    unassigned: String(props.filters.unassigned ?? ""),
    overdue: String(props.filters.overdue ?? ""),
    search: String(props.filters.search ?? ""),
    status: String(props.filters.status ?? ""),
    source: String(props.filters.source ?? ""),
    owner: String(props.filters.owner ?? ""),
  }),
  resetFilters: { unassigned: "", overdue: "", search: "", status: "", source: "", owner: "" },
  preservedQuery: () => props.filters,
  only: ["leads", "filters"],
});
const columns = createLeadIndexColumns();
</script>

<template>
  <WorkspacePage id="crm-leads" title="CRM leads">
    <template #actions>
      <UButton v-if="canManage" :to="create().url" icon="i-ph-plus" label="New lead" />
    </template>

    <div class="flex flex-wrap items-center gap-3">
      <UButton :to="index({ query: { unassigned: 1 } }).url" color="neutral" :variant="filters.unassigned ? 'solid' : 'outline'" label="Unassigned leads" />
      <UButton :to="index({ query: { overdue: 1 } }).url" color="neutral" :variant="filters.overdue ? 'solid' : 'outline'" label="Leads with overdue follow-ups" />
      <UButton v-if="hasActiveFilters" color="neutral" variant="ghost" label="Clear filters" @click="reset" />
    </div>
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
      <FormInput id="lead-search" v-model="filters.search" class="w-full sm:max-w-sm" icon="i-ph-magnifying-glass" label="Search contacts or organizations" />
      <FormSelect v-model="filters.status" :items="statuses" class="w-full sm:w-44" label="Status" placeholder="All statuses" />
      <FormSelect v-model="filters.source" :items="sources" class="w-full sm:w-44" label="Source" placeholder="All sources" />
      <FormSelect v-if="canAssign" v-model="filters.owner" :items="ownerOptions" class="w-full sm:w-48" label="Owner" placeholder="All owners" />
    </div>

    <IndexTableFrame v-if="leads.data.length" :loading="isLoading">
      <UTable :columns="columns" :data="leads.data" :loading="isLoading" class="w-full" />
    </IndexTableFrame>
    <IndexEmptyState v-else :loading="isLoading" :show-reset="hasActiveFilters" description="Try changing the filters or create the first lead." icon="i-ph-address-book" title="No leads found" @reset="reset">
      <template #actions><UButton v-if="canManage" :to="create().url" label="Create lead" /></template>
    </IndexEmptyState>

    <IndexPagination :meta="leads.meta" item-label="leads" @page="visitPage" />
  </WorkspacePage>
</template>
