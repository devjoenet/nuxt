import type { TableColumn } from "@nuxt/ui";
import UButton from "@nuxt/ui/components/Button.vue";
import { h } from "vue";
import { show } from "@/routes/crm/contacts";
import type { Contact } from "@/types/crm";

export function createContactIndexColumns(): TableColumn<Contact>[] {
  return [
    {
      accessorKey: "name",
      header: "Contact",
      cell: ({ row }) => h("div", {}, [h("p", { class: "font-medium text-highlighted" }, row.original.name), h("p", { class: "text-muted" }, row.original.email)]),
    },
    {
      accessorKey: "organization.name",
      header: "Organization",
      cell: ({ row }) => row.original.organization?.name ?? "Independent",
    },
    {
      accessorKey: "phone",
      header: "Phone",
      cell: ({ row }) => row.original.phone ?? "—",
    },
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
