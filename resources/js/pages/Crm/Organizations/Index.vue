<script lang="ts" setup>
import { ownerOptions as mapOwnerOptions, type OwnerOption } from "@southeastcode/crm/public";
import { FormInput } from "@/components/Ui/FormInput";
import { FormSelect } from "@/components/Ui/FormSelect";
import { IndexEmptyState } from "@/components/IndexEmptyState";
import { IndexPagination } from "@/components/IndexPagination";
import { IndexTableFrame } from "@/components/IndexTableFrame";
import { useInertiaFilters } from "@/composables/useInertiaFilters";
import { create, index } from "@/routes/crm/organizations";
import { createOrganizationIndexColumns } from "@/pages/Crm/Organizations/Components/organizationIndexColumns";
import type { Option, Organization, Paginated } from "@southeastcode/crm/public";

const props = defineProps<{ organizations: Paginated<Organization>; filters: Record<string, string | number>; owners: OwnerOption[]; canManage: boolean; canAssign: boolean }>();
const ownerOptions = computed<Option[]>(() => mapOwnerOptions(props.owners, true));
const { filters, hasActiveFilters, isLoading, reset, resetFilter, visitPage } = useInertiaFilters({
  url: () => index().url,
  initialFilters: () => ({
    search: String(props.filters.search ?? ""),
    owner: String(props.filters.owner ?? ""),
  }),
  resetFilters: { search: "", owner: "" },
  preservedQuery: () => props.filters,
  only: ["organizations", "filters"],
});
const columns = createOrganizationIndexColumns();
</script>

<template>
  <WorkspacePage id="crm-organizations" title="Organizations">
    <template #actions>
      <UButton v-if="canManage" :to="create().url" icon="i-ph-plus" label="New organization" />
    </template>

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
      <FormInput id="organization-search" v-model="filters.search" class="w-full sm:max-w-sm" icon="i-ph-magnifying-glass" label="Search organizations" />
      <div v-if="canAssign" class="flex w-full items-end gap-1 sm:w-auto">
        <FormSelect id="organization-owner-filter" v-model="filters.owner" :items="ownerOptions" class="min-w-0 flex-1 sm:w-48 sm:flex-none" label="Owner" placeholder="All owners" />
        <UButton v-if="filters.owner" aria-label="Clear owner filter" class="shrink-0" color="neutral" icon="i-ph-x" square type="button" variant="ghost" @click="resetFilter('owner')" />
      </div>
    </div>

    <IndexTableFrame v-if="organizations.data.length" :loading="isLoading">
      <UTable :columns="columns" :data="organizations.data" :loading="isLoading" class="w-full" />
    </IndexTableFrame>
    <IndexEmptyState v-else :loading="isLoading" :show-reset="hasActiveFilters" description="Create an organization to keep client context in one place." icon="i-ph-buildings" title="No organizations found" @reset="reset">
      <template #actions><UButton v-if="canManage" :to="create().url" label="Create organization" /></template>
    </IndexEmptyState>

    <IndexPagination :meta="organizations.meta" item-label="organizations" @page="visitPage" />
  </WorkspacePage>
</template>
