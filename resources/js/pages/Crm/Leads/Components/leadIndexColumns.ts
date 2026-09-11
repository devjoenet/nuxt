import type { TableColumn } from "@nuxt/ui";
import UButton from "@nuxt/ui/components/Button.vue";
import { h } from "vue";
import { LeadStatusBadge } from "@/components/LeadStatusBadge";
import { show } from "@/routes/crm/leads";
import type { Lead } from "@/types/crm";

export function createLeadIndexColumns(): TableColumn<Lead>[] {
  return [
    {
      accessorKey: "contact.name",
      header: "Contact",
      cell: ({ row }) =>
        h("div", { class: "min-w-48" }, [
          h("p", { class: "font-medium text-highlighted" }, row.original.contact?.name ?? "Contact unavailable"),
          h("p", { class: "text-muted" }, row.original.contact?.email ?? "Unavailable"),
          row.original.contact?.deleted_at ? h("p", { class: "text-warning" }, "Deleted contact") : null,
        ]),
    },
    {
      accessorKey: "organization.name",
      header: "Organization",
      cell: ({ row }) => row.original.organization?.name ?? "Independent",
    },
    {
      accessorKey: "status",
      header: "Status",
      cell: ({ row }) => h(LeadStatusBadge, { status: row.original.status }),
    },
    {
      accessorKey: "source",
      header: "Source",
      cell: ({ row }) => h("span", { class: "capitalize" }, row.original.source),
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
