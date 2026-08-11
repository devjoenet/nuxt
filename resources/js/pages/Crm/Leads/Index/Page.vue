<script lang="ts" setup>
import IndexEmptyState from "@/components/IndexEmptyState.vue";
import IndexPagination from "@/components/IndexPagination.vue";
import IndexTableFrame from "@/components/IndexTableFrame.vue";
import FormInput from "@/components/FormInput.vue";
import FormSelect from "@/components/FormSelect.vue";
import { useInertiaFilters } from "@/composables/useInertiaFilters";
import { create, index } from "@/routes/crm/leads";
import { createLeadIndexColumns } from "./leadIndexColumns";
import type { Lead, Option, Owner, Paginated } from "@/types/crm";

const props = defineProps<{
  leads: Paginated<Lead>;
  filters: Record<string, string | number>;
  statuses: Option[];
  sources: Option[];
  owners: Owner[];
  canManage: boolean;
  canAssign: boolean;
}>();
const ownerOptions = computed<Option[]>(() => props.owners.map((owner) => ({ label: owner.name, value: String(owner.id) })));
const { filters, hasActiveFilters, isLoading, reset, visitPage } = useInertiaFilters({
  url: () => index().url,
  initialFilters: () => ({
    search: String(props.filters.search ?? ""),
    status: String(props.filters.status ?? ""),
    source: String(props.filters.source ?? ""),
    owner: String(props.filters.owner ?? ""),
  }),
  resetFilters: { search: "", status: "", source: "", owner: "" },
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
