<script lang="ts" setup>
import { router } from "@inertiajs/vue3";
import { RecordHistoryButton } from "@southeastcode/audit/public";
import { ConfirmActionButton } from "@/components/ConfirmActionButton";
import { CrmDetailLayout } from "@/components/CrmDetailLayout";
import { CrmDetailMetadata } from "@/components/CrmDetailMetadata";
import { CrmRecordStatusBadge } from "@/components/CrmRecordStatusBadge";
import { formatDate } from "@/lib/dateFormatters";
import { archive, destroy, edit, history, index, restore } from "@/routes/crm/contacts";
import { show as organizationShow } from "@/routes/crm/organizations";
import type { Contact, CrmDetailMetadataItem } from "@/types/crm";

const props = defineProps<{ contact: Contact; canManage: boolean; canViewHistory: boolean }>();

const contactInformation = computed<CrmDetailMetadataItem[]>(() => [
  { label: "Email", value: props.contact.email, href: `mailto:${props.contact.email}` },
  props.contact.phone ? { label: "Phone", value: props.contact.phone, href: `tel:${props.contact.phone}` } : { label: "Phone", value: "No phone recorded" },
  props.contact.organization ? { label: "Organization", value: props.contact.organization.name, to: organizationShow(props.contact.organization.id).url } : { label: "Organization", value: "Independent contact" },
]);
const relationshipDetails = computed<CrmDetailMetadataItem[]>(() => [
  { label: "Owner", value: props.contact.owner?.name ?? "Unassigned" },
  { label: "Related leads", value: props.contact.leads_count ?? 0 },
  { label: "Added", value: formatDate(props.contact.created_at) },
]);

const archiveContact = (): void => router.post(archive(props.contact.id).url);
const restoreContact = (): void => router.delete(restore(props.contact.id).url);
const deleteContact = (): void => router.delete(destroy(props.contact.id).url);
</script>

<template>
  <CrmDetailLayout :document-title="contact.name" :edit-url="canManage ? edit(contact.id).url : undefined" :index-url="index().url" index-label="All contacts" navbar-title="Contact details" panel-id="crm-contact-detail">
    <template #summary>
      <div class="min-w-0" data-testid="contact-summary">
        <div class="flex flex-wrap items-start gap-3">
          <h1 class="text-2xl font-semibold tracking-tight break-words text-highlighted">{{ contact.name }}</h1>
          <CrmRecordStatusBadge v-if="contact.archived_at" data-testid="contact-archived-status" status="archived" />
        </div>
      </div>
    </template>

    <section aria-labelledby="contact-information-heading">
      <h2 id="contact-information-heading" class="text-xl font-semibold text-highlighted">Contact information</h2>
      <CrmDetailMetadata :items="contactInformation" class="mt-4" layout="rows" />
    </section>

    <template #aside>
      <div class="space-y-8">
        <section aria-labelledby="contact-relationship-details-heading">
          <h2 id="contact-relationship-details-heading" class="text-lg font-semibold text-highlighted">Relationship details</h2>
          <CrmDetailMetadata :items="relationshipDetails" class="mt-4" />
        </section>
        <div v-if="canManage || canViewHistory" class="flex flex-wrap gap-2 border-t border-default pt-6">
          <RecordHistoryButton v-if="canViewHistory" :href="history(contact.id).url" />
          <template v-if="canManage">
            <ConfirmActionButton v-if="!contact.archived_at" confirm-label="Archive contact" description="The contact will leave active CRM results and can be restored later." label="Archive" title="Archive this contact?" @confirm="archiveContact" />
            <ConfirmActionButton v-else color="neutral" confirm-color="primary" confirm-label="Restore contact" description="The contact will return to active CRM results." label="Restore" title="Restore this contact?" variant="outline" @confirm="restoreContact" />
            <ConfirmActionButton confirm-label="Delete contact" description="The contact will be removed from active CRM results. Related history remains available where required." label="Delete" title="Delete this contact?" variant="ghost" @confirm="deleteContact" />
          </template>
        </div>
      </div>
    </template>
  </CrmDetailLayout>
</template>
