import type { TableColumn } from "@nuxt/ui";
import { h, resolveComponent } from "vue";
import { show } from "@/routes/crm/organizations";
import type { Organization } from "@/types/crm";

export function createOrganizationIndexColumns(): TableColumn<Organization>[] {
  const UButton = resolveComponent("UButton");

  return [
    {
      accessorKey: "name",
      header: "Organization",
      cell: ({ row }) => h("div", {}, [h("p", { class: "font-medium text-highlighted" }, row.original.name), h("p", { class: "text-muted" }, row.original.website ?? "No website")]),
    },
    { accessorKey: "contacts_count", header: "Contacts" },
    { accessorKey: "leads_count", header: "Leads" },
    {
      accessorKey: "owner.name",
      header: "Owner",
      cell: ({ row }) => row.original.owner?.name ?? "Unassigned",
    },
    {
      id: "actions",
      cell: ({ row }) => h(UButton, { to: show(row.original.id).url, label: "View", color: "neutral", variant: "ghost", size: "sm" }),
    },
  ];
}
