<script lang="ts" setup>
  import { router } from "@inertiajs/vue3";
  import type { TableColumn } from "@nuxt/ui";
  import { h, resolveComponent, watch } from "vue";
  import FormInput from "@/components/FormInput.vue";
  import FormSelect from "@/components/FormSelect.vue";
  import Layout from "@/layouts/Default.vue";
  import { create, index, show } from "@/routes/crm/organizations";
  import type { Option, Organization, Owner, Paginated } from "@/types/crm";

  defineOptions({ layout: Layout });
  const props = defineProps<{ organizations: Paginated<Organization>; filters: Record<string, string>; owners: Owner[]; canManage: boolean; canAssign: boolean }>();
  const UButton = resolveComponent("UButton");
  const search = ref(props.filters.search ?? "");
  const owner = ref(props.filters.owner ?? "");
  let timer: ReturnType<typeof setTimeout>;
  const ownerOptions = computed<Option[]>(() => props.owners.map((item) => ({ label: item.name, value: String(item.id) })));
  const clearOwnerFilter = (): void => {
    owner.value = "";
  };

  const visit = () => router.get(index().url, { search: search.value || undefined, owner: owner.value || undefined }, { preserveState: true, replace: true });
  watch([search, owner], () => {
    clearTimeout(timer);
    timer = setTimeout(visit, 250);
  });

  const columns: TableColumn<Organization>[] = [
    {
      accessorKey: "name",
      header: "Organization",
      cell: ({ row }) =>
        h("div", {}, [h("p", { class: "font-medium text-highlighted" }, row.original.name), h("p", { class: "text-muted" }, row.original.website ?? "No website")]),
    },
    { accessorKey: "contacts_count", header: "Contacts" },
    { accessorKey: "leads_count", header: "Leads" },
    { accessorKey: "owner.name", header: "Owner", cell: ({ row }) => row.original.owner?.name ?? "Unassigned" },
    { id: "actions", cell: ({ row }) => h(UButton, { to: show(row.original.id).url, label: "View", color: "neutral", variant: "ghost", size: "sm" }) },
  ];
</script>

<template>
  <UDashboardPanel id="crm-organizations">
    <template #header>
      <UDashboardNavbar title="Organizations">
        <template #leading><UDashboardSidebarCollapse /></template>
        <template #right><UButton v-if="canManage" :to="create().url" icon="i-ph-plus" label="New organization" /></template>
      </UDashboardNavbar>
    </template>
    <template #body>
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
        <FormInput v-model="search" class="w-full sm:max-w-sm" icon="i-ph-magnifying-glass" label="Search organizations" />
        <div v-if="canAssign" class="flex w-full items-end gap-1 sm:w-auto">
          <FormSelect id="organization-owner-filter" v-model="owner" :items="ownerOptions" class="min-w-0 flex-1 sm:w-48 sm:flex-none" label="Owner" placeholder="All owners" />
          <UButton v-if="owner" aria-label="Clear owner filter" class="shrink-0" color="neutral" icon="i-ph-x" square type="button" variant="ghost" @click="clearOwnerFilter" />
        </div>
      </div>
      <div v-if="organizations.data.length" class="-mx-4 -my-2 overflow-x-auto whitespace-nowrap sm:-mx-6 lg:-mx-8">
        <div class="inline-block min-w-full px-4 py-2 align-middle sm:px-6 lg:px-8"><UTable :columns="columns" :data="organizations.data" class="w-full" /></div>
      </div>
      <UEmpty v-else description="Create an organization to keep client context in one place." icon="i-ph-buildings" title="No organizations found">
        <template #actions><UButton v-if="canManage" :to="create().url" label="Create organization" /></template>
      </UEmpty>
      <div v-if="organizations.meta.last_page > 1" class="flex items-center justify-between border-t border-default pt-4">
        <p class="text-base text-muted sm:text-sm">{{ organizations.meta.total }} organizations</p>
        <UPagination
          :items-per-page="organizations.meta.per_page"
          :page="organizations.meta.current_page"
          :total="organizations.meta.total"
          @update:page="router.get(index({ query: { ...filters, page: $event } }).url)"
        />
      </div>
    </template>
  </UDashboardPanel>
</template>
