<script setup lang="ts">
import { Link, router } from "@inertiajs/vue3";
import { ConfirmActionButton } from "@/components/ConfirmActionButton";
import { CrmDetailMetadata } from "@/components/CrmDetailMetadata";
import { CrmRecordStatusBadge } from "@/components/CrmRecordStatusBadge";
import { LeadStatusBadge } from "@/components/LeadStatusBadge";
import { formatDateTime } from "@/lib/dateFormatters";
import { cn } from "@/lib/utils";
import { show as contactShow } from "@/routes/crm/contacts";
import { archive, convert, restore } from "@/routes/crm/leads";
import type { CrmDetailMetadataItem, Lead } from "@/types/crm";

const props = defineProps<{
  lead: Lead;
  canManage: boolean;
  class?: string;
}>();

const overviewMetadata = computed<CrmDetailMetadataItem[]>(() => [
  { label: "Email", value: props.lead.contact?.email ?? "Unavailable" },
  { label: "Phone", value: props.lead.contact?.phone ?? "Not provided" },
  { label: "Owner", value: props.lead.owner?.name ?? "Unassigned" },
  { label: "Created", value: formatDateTime(props.lead.created_at) },
]);

const archiveLead = (): void => router.post(archive(props.lead.id).url);
const restoreLead = (): void => router.delete(restore(props.lead.id).url);
const convertLead = (): void => router.post(convert(props.lead.id).url);
</script>

<template>
  <section :class="cn('grid gap-4', props.class)">
    <div class="flex flex-wrap items-start justify-between gap-4">
      <div>
        <div class="flex flex-wrap items-center gap-3">
          <Link v-if="lead.contact && !lead.contact.deleted_at" :href="contactShow(lead.contact.id).url" class="text-2xl font-semibold tracking-tight text-balance text-highlighted hover:underline">
            {{ lead.contact.name }}
          </Link>
          <h1 v-else class="text-2xl font-semibold tracking-tight text-balance text-highlighted">{{ lead.contact?.name ?? "Contact unavailable" }}</h1>
          <CrmRecordStatusBadge v-if="lead.contact?.deleted_at" data-testid="lead-deleted-contact-status" label="Deleted contact" status="deleted" />
          <LeadStatusBadge :status="lead.status" />
        </div>
        <p class="text-base text-pretty text-muted sm:text-sm">{{ lead.organization?.name ?? "Independent contact" }} · {{ lead.source }}</p>
      </div>
      <div v-if="canManage" class="flex flex-wrap gap-2">
        <ConfirmActionButton
          v-if="!lead.converted_at"
          color="neutral"
          confirm-color="primary"
          confirm-label="Convert to client"
          description="The organization connected to this lead will be marked as a client."
          label="Convert to client"
          title="Convert this organization to a client?"
          @confirm="convertLead"
        />
        <ConfirmActionButton v-if="!lead.archived_at" confirm-label="Archive lead" description="The lead will leave active CRM results and can be restored later." label="Archive" title="Archive this lead?" @confirm="archiveLead" />
        <ConfirmActionButton v-else color="neutral" confirm-color="primary" confirm-label="Restore lead" description="The lead will return to active CRM results." label="Restore" title="Restore this lead?" variant="outline" @confirm="restoreLead" />
      </div>
    </div>
    <CrmDetailMetadata :items="overviewMetadata" layout="grid" />
    <div>
      <h2 class="font-semibold text-highlighted">Project details</h2>
      <p class="mt-2 text-base text-pretty whitespace-pre-wrap text-muted sm:text-sm">{{ lead.project_details }}</p>
    </div>
  </section>
</template>
