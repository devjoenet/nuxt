<script lang="ts" setup>
import { Link, router } from "@inertiajs/vue3";
import CrmDetailLayout from "@/components/crm/CrmDetailLayout.vue";
import { archive, destroy, edit, index, restore } from "@/routes/crm/contacts";
import { show as organizationShow } from "@/routes/crm/organizations";
import type { Contact } from "@/types/crm";

const props = defineProps<{ contact: Contact; canManage: boolean }>();
const confirmArchive = () => {
  if (window.confirm("Archive this contact?")) router.post(archive(props.contact.id).url);
};
const confirmRestore = () => {
  if (window.confirm("Restore this contact?")) router.delete(restore(props.contact.id).url);
};
const confirmDelete = () => {
  if (window.confirm("Delete this contact? This removes it from active CRM results.")) router.delete(destroy(props.contact.id).url);
};
const formatDate = (value: string | null): string => (value ? new Intl.DateTimeFormat("en-US", { dateStyle: "medium" }).format(new Date(value)) : "Not recorded");
</script>

<template>
  <CrmDetailLayout :edit-url="canManage ? edit(contact.id).url : undefined" :index-url="index().url" index-label="All contacts" navbar-title="Contact details" panel-id="crm-contact-detail">
    <template #summary>
      <div class="min-w-0" data-testid="contact-summary">
        <div class="flex flex-wrap items-start gap-3">
          <h1 class="break-words text-2xl font-semibold tracking-tight text-highlighted">{{ contact.name }}</h1>
          <UBadge v-if="contact.archived_at" color="warning" data-testid="contact-archived-status" label="Archived" variant="subtle" />
        </div>
      </div>
    </template>

    <section aria-labelledby="contact-information-heading">
      <h2 id="contact-information-heading" class="text-xl font-semibold text-highlighted">Contact information</h2>
      <dl class="mt-4 divide-y divide-default border-y border-default">
        <div class="grid gap-1 py-4 sm:grid-cols-[10rem_minmax(0,1fr)] sm:gap-4">
          <dt class="text-base text-muted sm:text-sm">Email</dt>
          <dd>
            <a :href="`mailto:${contact.email}`" class="break-all font-medium text-highlighted hover:underline">{{ contact.email }}</a>
          </dd>
        </div>
        <div class="grid gap-1 py-4 sm:grid-cols-[10rem_minmax(0,1fr)] sm:gap-4">
          <dt class="text-base text-muted sm:text-sm">Phone</dt>
          <dd>
            <a v-if="contact.phone" :href="`tel:${contact.phone}`" class="font-medium text-highlighted hover:underline">{{ contact.phone }}</a
            ><span v-else class="font-medium text-highlighted">No phone recorded</span>
          </dd>
        </div>
        <div class="grid gap-1 py-4 sm:grid-cols-[10rem_minmax(0,1fr)] sm:gap-4">
          <dt class="text-base text-muted sm:text-sm">Organization</dt>
          <dd>
            <Link v-if="contact.organization" :href="organizationShow(contact.organization.id).url" class="font-medium text-highlighted hover:underline">{{ contact.organization.name }}</Link
            ><span v-else class="font-medium text-highlighted">Independent contact</span>
          </dd>
        </div>
      </dl>
    </section>

    <template #aside>
      <div class="space-y-8">
        <section aria-labelledby="contact-relationship-details-heading">
          <h2 id="contact-relationship-details-heading" class="text-lg font-semibold text-highlighted">Relationship details</h2>
          <dl class="mt-4 space-y-4">
            <div>
              <dt class="text-base text-muted sm:text-sm">Owner</dt>
              <dd class="mt-1 font-medium text-highlighted">{{ contact.owner?.name ?? "Unassigned" }}</dd>
            </div>
            <div>
              <dt class="text-base text-muted sm:text-sm">Related leads</dt>
              <dd class="mt-1 font-medium text-highlighted">{{ contact.leads_count ?? 0 }}</dd>
            </div>
            <div>
              <dt class="text-base text-muted sm:text-sm">Added</dt>
              <dd class="mt-1 font-medium text-highlighted">{{ formatDate(contact.created_at) }}</dd>
            </div>
          </dl>
        </section>
        <div v-if="canManage" class="flex flex-wrap gap-2 border-t border-default pt-6">
          <UButton v-if="!contact.archived_at" color="error" label="Archive" variant="soft" @click="confirmArchive" />
          <UButton v-else color="neutral" label="Restore" variant="outline" @click="confirmRestore" />
          <UButton color="error" label="Delete" variant="ghost" @click="confirmDelete" />
        </div>
      </div>
    </template>
  </CrmDetailLayout>
</template>
