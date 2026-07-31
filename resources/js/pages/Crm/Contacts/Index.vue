<script lang="ts" setup>
import { router } from "@inertiajs/vue3";
import type { TableColumn } from "@nuxt/ui";
import { h, resolveComponent, watch } from "vue";
import AsyncCrmSelect from "@/components/AsyncCrmSelect.vue";
import FormInput from "@/components/FormInput.vue";
import FormSelect from "@/components/FormSelect.vue";
import Layout from "@/layouts/Default.vue";
import { create, index, show } from "@/routes/crm/contacts";
import type { Contact, Option, Owner, Paginated } from "@/types/crm";

defineOptions({ layout: Layout });
const props = defineProps<{
  contacts: Paginated<Contact>;
  filters: Record<string, string>;
  owners: Owner[];
  canManage: boolean;
  canAssign: boolean;
}>();
const UButton = resolveComponent("UButton");
const search = ref(props.filters.search ?? "");
const organization = ref(props.filters.organization ?? "");
const owner = ref(props.filters.owner ?? "");
let timer: ReturnType<typeof setTimeout>;
const ownerOptions = computed<Option[]>(() => props.owners.map((item) => ({ label: item.name, value: String(item.id) })));
const visit = () => router.get(index().url, { search: search.value || undefined, organization: organization.value || undefined, owner: owner.value || undefined }, { preserveState: true, replace: true });
watch([search, organization, owner], () => {
  clearTimeout(timer);
  timer = setTimeout(visit, 250);
});
const columns: TableColumn<Contact>[] = [
  {
    accessorKey: "name",
    header: "Contact",
    cell: ({ row }) => h("div", {}, [h("p", { class: "font-medium text-highlighted" }, row.original.name), h("p", { class: "text-muted" }, row.original.email)]),
  },
  { accessorKey: "organization.name", header: "Organization", cell: ({ row }) => row.original.organization?.name ?? "Independent" },
  { accessorKey: "phone", header: "Phone", cell: ({ row }) => row.original.phone ?? "—" },
  { accessorKey: "owner.name", header: "Owner", cell: ({ row }) => row.original.owner?.name ?? "Unassigned" },
  { id: "actions", cell: ({ row }) => h(UButton, { to: show(row.original.id).url, label: "View", color: "neutral", variant: "ghost", size: "sm" }) },
];
</script>

<template>
  <UDashboardPanel id="crm-contacts">
    <template #header
      ><UDashboardNavbar title="Contacts"
        ><template #leading><UDashboardSidebarCollapse /></template><template #right><UButton v-if="canManage" :to="create().url" icon="i-ph-plus" label="New contact" /></template></UDashboardNavbar
    ></template>
    <template #body>
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
        <FormInput v-model="search" class="w-full sm:max-w-sm" icon="i-ph-magnifying-glass" label="Search contacts" /><AsyncCrmSelect
          id="contact-organization-filter"
          v-model="organization"
          class="w-full sm:w-52"
          clearable
          label="Organization"
          placeholder="All organizations"
          name="organization"
          type="organization"
        /><FormSelect v-if="canAssign" v-model="owner" :items="ownerOptions" class="w-full sm:w-48" label="Owner" placeholder="All owners" />
      </div>
      <div v-if="contacts.data.length" class="-mx-4 -my-2 overflow-x-auto whitespace-nowrap sm:-mx-6 lg:-mx-8">
        <div class="inline-block min-w-full px-4 py-2 align-middle sm:px-6 lg:px-8"><UTable :columns="columns" :data="contacts.data" class="w-full" /></div>
      </div>
      <UEmpty v-else description="Create a contact to keep the people behind each account visible." icon="i-ph-user-circle" title="No contacts found"
        ><template #actions><UButton v-if="canManage" :to="create().url" label="Create contact" /></template
      ></UEmpty>
      <div v-if="contacts.meta.last_page > 1" class="flex items-center justify-between border-t border-default pt-4">
        <p class="text-base text-muted sm:text-sm">{{ contacts.meta.total }} contacts</p>
        <UPagination :items-per-page="contacts.meta.per_page" :page="contacts.meta.current_page" :total="contacts.meta.total" @update:page="router.get(index({ query: { ...filters, page: $event } }).url)" />
      </div>
    </template>
  </UDashboardPanel>
</template>
