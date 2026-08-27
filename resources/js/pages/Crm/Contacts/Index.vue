<script lang="ts" setup>
import { AsyncCrmSelect } from "@/components/AsyncCrmSelect";
import { FormInput } from "@/components/Ui/FormInput";
import { FormSelect } from "@/components/Ui/FormSelect";
import IndexEmptyState from "@/components/IndexEmptyState.vue";
import IndexPagination from "@/components/IndexPagination.vue";
import IndexTableFrame from "@/components/IndexTableFrame.vue";
import { useInertiaFilters } from "@/composables/useInertiaFilters";
import { create, index } from "@/routes/crm/contacts";
import { createContactIndexColumns } from "@/sections/Crm/Contacts/contactIndexColumns";
import type { Contact, Option, Owner, Paginated } from "@/types/crm";

const props = defineProps<{
  contacts: Paginated<Contact>;
  filters: Record<string, string | number>;
  owners: Owner[];
  canManage: boolean;
  canAssign: boolean;
}>();
const ownerOptions = computed<Option[]>(() => props.owners.map((item) => ({ label: item.name, value: String(item.id) })));
const { filters, hasActiveFilters, isLoading, reset, visitPage } = useInertiaFilters({
  url: () => index().url,
  initialFilters: () => ({
    search: String(props.filters.search ?? ""),
    organization: String(props.filters.organization ?? ""),
    owner: String(props.filters.owner ?? ""),
  }),
  resetFilters: { search: "", organization: "", owner: "" },
  preservedQuery: () => props.filters,
  only: ["contacts", "filters"],
});
const columns = createContactIndexColumns();
</script>

<template>
  <WorkspacePage id="crm-contacts" title="Contacts">
    <template #actions>
      <UButton v-if="canManage" :to="create().url" icon="i-ph-plus" label="New contact" />
    </template>

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
      <FormInput id="contact-search" v-model="filters.search" class="w-full sm:max-w-sm" icon="i-ph-magnifying-glass" label="Search contacts" /><AsyncCrmSelect
        id="contact-organization-filter"
        v-model="filters.organization"
        class="w-full sm:w-52"
        clearable
        label="Organization"
        name="organization"
        placeholder="All organizations"
        type="organization"
      /><FormSelect v-if="canAssign" v-model="filters.owner" :items="ownerOptions" class="w-full sm:w-48" label="Owner" placeholder="All owners" />
    </div>

    <IndexTableFrame v-if="contacts.data.length" :loading="isLoading">
      <UTable :columns="columns" :data="contacts.data" :loading="isLoading" class="w-full" />
    </IndexTableFrame>
    <IndexEmptyState v-else :loading="isLoading" :show-reset="hasActiveFilters" description="Create a contact to keep the people behind each account visible." icon="i-ph-user-circle" title="No contacts found" @reset="reset">
      <template #actions><UButton v-if="canManage" :to="create().url" label="Create contact" /></template>
    </IndexEmptyState>

    <IndexPagination :meta="contacts.meta" item-label="contacts" @page="visitPage" />
  </WorkspacePage>
</template>
