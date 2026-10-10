import type { TableColumn } from "@nuxt/ui";
import { Link } from "@inertiajs/vue3";
import { h } from "vue";
import { LeadStatusBadge } from "@/components/LeadStatusBadge";
import { show } from "@/routes/crm/leads";
//import { show as showContact } from "@/routes/crm/contacts";
import { show as showOrganization } from "@/routes/crm/organizations";
import type { Lead } from "@/types/crm";

export function createLeadIndexColumns(): TableColumn<Lead>[] {
  return [
    {
      accessorKey: "contact.name",
      header: "Contact",
      cell: ({ row }) =>
        h("div", { class: "min-w-48 flex flex-col space-y-3" }, [
          h(Link, { href: show(row.original.id).url, class: "block font-medium text-primary underline underline-offset-4" }, () => row.original.contact?.name ?? "Contact unavailable"),
          //h("p", { class: "text-xs text-muted" }, row.original.contact?.email ?? "Unavailable"),
          //row.original.contact && !row.original.contact.deleted_at ? h(Link, { href: showContact(row.original.contact.id).url, class: "block text-xs text-primary underline underline-offset-4", "aria-label": `View Contact: ${row.original.contact.name}` }, () => "View Contact") : null,
          //row.original.contact?.deleted_at ? h("p", { class: "text-warning" }, "Deleted contact") : null,
        ]),
    },
    {
      accessorKey: "organization.name",
      header: "Organization",
      cell: ({ row }) => (row.original.organization ? h(Link, { href: showOrganization(row.original.organization.id).url, class: "text-primary underline underline-offset-4" }, () => row.original.organization?.name) : "Independent"),
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
  ];
}
