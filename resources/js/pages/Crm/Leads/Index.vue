<script lang="ts" setup>
  import { Link, router } from "@inertiajs/vue3";
  import FormInput from "@/components/FormInput.vue";
  import FormSelect from "@/components/FormSelect.vue";
  import type { TableColumn } from "@nuxt/ui";
  import { h, resolveComponent, watch } from "vue";
  import Layout from "@/layouts/Default.vue";
  import LeadStatusBadge from "@/components/crm/LeadStatusBadge.vue";
  import { create, index, show } from "@/routes/crm/leads";
  import type { Lead, Option, Owner, Paginated } from "@/types/crm";

  defineOptions({ layout: Layout });
  const props = defineProps<{ leads: Paginated<Lead>; filters: Record<string, string>; statuses: Option[]; sources: Option[]; owners: Owner[]; canManage: boolean }>();
  const UButton = resolveComponent("UButton");
  const search = ref(props.filters.search ?? "");
  const status = ref(props.filters.status ?? "");
  const source = ref(props.filters.source ?? "");
  let timer: ReturnType<typeof setTimeout>;

  const visit = () =>
    router.get(index().url, { search: search.value || undefined, status: status.value || undefined, source: source.value || undefined }, { preserveState: true, replace: true });
  watch([search, status, source], () => {
    clearTimeout(timer);
    timer = setTimeout(visit, 250);
  });

  const columns: TableColumn<Lead>[] = [
    {
      accessorKey: "contact.name",
      header: "Contact",
      cell: ({ row }) =>
        h("div", { class: "min-w-48" }, [
          h("p", { class: "font-medium text-highlighted" }, row.original.contact.name),
          h("p", { class: "text-muted" }, row.original.contact.email),
        ]),
    },
    { accessorKey: "organization.name", header: "Organization", cell: ({ row }) => row.original.organization?.name ?? "Independent" },
    { accessorKey: "status", header: "Status", cell: ({ row }) => h(LeadStatusBadge, { status: row.original.status }) },
    { accessorKey: "source", header: "Source", cell: ({ row }) => h("span", { class: "capitalize" }, row.original.source) },
    { accessorKey: "owner.name", header: "Owner", cell: ({ row }) => row.original.owner?.name ?? "Unassigned" },
    { id: "actions", cell: ({ row }) => h(UButton, { as: Link, href: show(row.original.id), label: "View", color: "neutral", variant: "ghost", size: "sm" }) },
  ];
</script>

<template>
  <UDashboardPanel id="crm-leads">
    <template #header>
      <UDashboardNavbar title="CRM leads">
        <template #leading><UDashboardSidebarCollapse /></template>
        <template #right><UButton v-if="canManage" :as="Link" :href="create()" icon="i-ph-plus" label="New lead" /></template>
      </UDashboardNavbar>
    </template>
    <template #body>
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
        <FormInput v-model="search" class="w-full sm:max-w-sm" icon="i-ph-magnifying-glass" label="Search contacts or organizations" />
        <FormSelect v-model="status" :items="[{ label: 'All statuses', value: '' }, ...statuses]" class="w-full sm:w-44" label="Status" />
        <FormSelect v-model="source" :items="[{ label: 'All sources', value: '' }, ...sources]" class="w-full sm:w-44" label="Source" />
      </div>
      <div v-if="leads.data.length" class="-mx-4 -my-2 overflow-x-auto whitespace-nowrap sm:-mx-6 lg:-mx-8">
        <div class="inline-block min-w-full px-4 py-2 align-middle sm:px-6 lg:px-8">
          <UTable :columns="columns" :data="leads.data" class="w-full" />
        </div>
      </div>
      <UEmpty v-else description="Try changing the filters or create the first lead." icon="i-ph-address-book" title="No leads found">
        <template #actions><UButton v-if="canManage" :as="Link" :href="create()" label="Create lead" /></template>
      </UEmpty>
      <div v-if="leads.meta.last_page > 1" class="flex items-center justify-between border-t border-default pt-4">
        <p class="text-base text-muted sm:text-sm">{{ leads.meta.total }} leads</p>
        <UPagination
          :items-per-page="leads.meta.per_page"
          :page="leads.meta.current_page"
          :total="leads.meta.total"
          @update:page="router.get(index({ query: { ...filters, page: $event } }).url)"
        />
      </div>
    </template>
  </UDashboardPanel>
</template>
