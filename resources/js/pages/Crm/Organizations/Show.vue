<script lang="ts" setup>
import { Link, router } from "@inertiajs/vue3";
import { RecordHistoryButton } from "@southeastcode/audit/public";
import { ConfirmActionButton } from "@/components/ConfirmActionButton";
import { CrmDetailLayout } from "@southeastcode/crm/public";
import { CrmDetailMetadata } from "@southeastcode/crm/public";
import { CrmRecordStatusBadge } from "@southeastcode/crm/public";
import { formatDate } from "@/lib/dateFormatters";
import { show as contactShow } from "@/routes/crm/contacts";
import { archive, destroy, edit, history, index, restore } from "@/routes/crm/organizations";
import type { CrmDetailMetadataItem, Organization } from "@southeastcode/crm/public";

const props = defineProps<{ organization: Organization; canManage: boolean; canViewHistory: boolean }>();

const accountDetails = computed<CrmDetailMetadataItem[]>(() => [
  { label: "Owner", value: props.organization.owner?.name ?? "Unassigned" },
  { label: "Contacts", value: props.organization.contacts_count ?? props.organization.contacts?.length ?? 0 },
  { label: "Leads", value: props.organization.leads_count ?? 0 },
  { label: "Client since", value: formatDate(props.organization.client_since_at) },
  { label: "Added", value: formatDate(props.organization.created_at) },
]);

const archiveOrganization = (): void => router.post(archive(props.organization.id).url);
const restoreOrganization = (): void => router.delete(restore(props.organization.id).url);
const deleteOrganization = (): void => router.delete(destroy(props.organization.id).url);
</script>

<template>
  <CrmDetailLayout :document-title="organization.name" :edit-url="canManage ? edit(organization.id).url : undefined" :index-url="index().url" index-label="All organizations" navbar-title="Organization details" panel-id="crm-organization-detail">
    <template #summary>
      <div class="min-w-0" data-testid="organization-summary">
        <div class="flex flex-wrap items-start gap-3">
          <h1 class="text-2xl font-semibold tracking-tight break-words text-highlighted">{{ organization.name }}</h1>
          <CrmRecordStatusBadge v-if="organization.archived_at" data-testid="organization-archived-status" status="archived" />
        </div>
        <a v-if="organization.website" :href="organization.website" class="mt-2 block text-base break-all text-muted hover:text-highlighted hover:underline sm:text-sm" rel="noreferrer" target="_blank">
          {{ organization.website }}
        </a>
        <p v-else class="mt-2 text-base text-muted sm:text-sm">No website recorded.</p>
      </div>
    </template>

    <section aria-labelledby="organization-contacts-heading" data-testid="organization-contacts">
      <div class="flex flex-wrap items-baseline justify-between gap-2">
        <h2 id="organization-contacts-heading" class="text-xl font-semibold text-highlighted">Contacts</h2>
        <p class="text-base text-muted sm:text-sm">{{ organization.contacts_count ?? organization.contacts?.length ?? 0 }} total</p>
      </div>
      <ul v-if="organization.contacts?.length" class="mt-4 divide-y divide-default border-y border-default" role="list">
        <li v-for="contact in organization.contacts" :key="contact.id" class="flex flex-col gap-3 py-4 sm:flex-row sm:items-center sm:justify-between">
          <div class="min-w-0 space-y-1">
            <Link :href="contactShow(contact.id).url" class="font-medium text-highlighted hover:underline">{{ contact.name }}</Link>
            <div class="flex flex-wrap gap-x-3 gap-y-1 text-base text-muted sm:text-sm">
              <a :href="`mailto:${contact.email}`" class="break-all hover:text-highlighted hover:underline">{{ contact.email }}</a>
              <a v-if="contact.phone" :href="`tel:${contact.phone}`" class="hover:text-highlighted hover:underline">{{ contact.phone }}</a>
              <span v-else>No phone recorded</span>
            </div>
          </div>
          <p class="shrink-0 text-base text-muted sm:text-sm"><span class="font-medium text-highlighted">Owner:</span> {{ contact.owner?.name ?? "Unassigned" }}</p>
        </li>
      </ul>
      <UEmpty v-else class="mt-4" description="Add a contact to keep the people behind this account visible." icon="i-ph-user-circle" title="No contacts recorded." />
    </section>

    <template #aside>
      <div class="space-y-8">
        <section aria-labelledby="organization-account-details-heading">
          <h2 id="organization-account-details-heading" class="text-lg font-semibold text-highlighted">Account details</h2>
          <CrmDetailMetadata :items="accountDetails" class="mt-4" />
        </section>
        <div v-if="canManage || canViewHistory" class="flex flex-wrap gap-2 border-t border-default pt-6">
          <RecordHistoryButton v-if="canViewHistory" :href="history(organization.id).url" />
          <template v-if="canManage">
            <ConfirmActionButton v-if="!organization.archived_at" confirm-label="Archive organization" description="The organization will leave active CRM results and can be restored later." label="Archive" title="Archive this organization?" @confirm="archiveOrganization" />
            <ConfirmActionButton v-else color="neutral" confirm-color="primary" confirm-label="Restore organization" description="The organization will return to active CRM results." label="Restore" title="Restore this organization?" variant="outline" @confirm="restoreOrganization" />
            <ConfirmActionButton
              confirm-label="Delete organization"
              description="The organization will be removed from active CRM results. Related history remains available where required."
              label="Delete"
              title="Delete this organization?"
              variant="ghost"
              @confirm="deleteOrganization"
            />
          </template>
        </div>
      </div>
    </template>
  </CrmDetailLayout>
</template>
