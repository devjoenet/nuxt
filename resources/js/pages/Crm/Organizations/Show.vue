<script lang="ts" setup>
  import { Link, router } from "@inertiajs/vue3";
  import CrmDetailLayout from "@/components/crm/CrmDetailLayout.vue";
  import Layout from "@/layouts/Default.vue";
  import { archive, destroy, edit, index, restore } from "@/routes/crm/organizations";
  import { show as contactShow } from "@/routes/crm/contacts";
  import type { Organization } from "@/types/crm";

  defineOptions({ layout: Layout });
  const props = defineProps<{ organization: Organization; canManage: boolean }>();
  const confirmArchive = () => {
    if (window.confirm("Archive this organization?")) router.post(archive(props.organization.id).url);
  };
  const confirmRestore = () => {
    if (window.confirm("Restore this organization?")) router.delete(restore(props.organization.id).url);
  };
  const confirmDelete = () => {
    if (window.confirm("Delete this organization? This removes it from active CRM results.")) router.delete(destroy(props.organization.id).url);
  };
  const formatDate = (value: string | null): string => (value ? new Intl.DateTimeFormat("en-US", { dateStyle: "medium" }).format(new Date(value)) : "Not recorded");
</script>

<template>
  <CrmDetailLayout
    :edit-url="canManage ? edit(organization.id).url : undefined"
    :index-url="index().url"
    index-label="All organizations"
    navbar-title="Organization details"
    panel-id="crm-organization-detail"
  >
    <template #summary>
      <div class="min-w-0" data-testid="organization-summary">
        <div class="flex flex-wrap items-start gap-3">
          <h1 class="break-words text-2xl font-semibold tracking-tight text-highlighted">{{ organization.name }}</h1>
          <UBadge v-if="organization.archived_at" color="warning" data-testid="organization-archived-status" label="Archived" variant="subtle" />
        </div>
        <a
          v-if="organization.website"
          :href="organization.website"
          class="mt-2 block break-all text-base text-muted hover:text-highlighted hover:underline sm:text-sm"
          rel="noreferrer"
          target="_blank"
        >
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
          <dl class="mt-4 space-y-4">
            <div>
              <dt class="text-base text-muted sm:text-sm">Owner</dt>
              <dd class="mt-1 font-medium text-highlighted">{{ organization.owner?.name ?? "Unassigned" }}</dd>
            </div>
            <div>
              <dt class="text-base text-muted sm:text-sm">Contacts</dt>
              <dd class="mt-1 font-medium text-highlighted">{{ organization.contacts_count ?? organization.contacts?.length ?? 0 }}</dd>
            </div>
            <div>
              <dt class="text-base text-muted sm:text-sm">Leads</dt>
              <dd class="mt-1 font-medium text-highlighted">{{ organization.leads_count ?? 0 }}</dd>
            </div>
            <div>
              <dt class="text-base text-muted sm:text-sm">Client since</dt>
              <dd class="mt-1 font-medium text-highlighted">{{ formatDate(organization.client_since_at) }}</dd>
            </div>
            <div>
              <dt class="text-base text-muted sm:text-sm">Added</dt>
              <dd class="mt-1 font-medium text-highlighted">{{ formatDate(organization.created_at) }}</dd>
            </div>
          </dl>
        </section>
        <div v-if="canManage" class="flex flex-wrap gap-2 border-t border-default pt-6">
          <UButton v-if="!organization.archived_at" color="error" label="Archive" variant="soft" @click="confirmArchive" />
          <UButton v-else color="neutral" label="Restore" variant="outline" @click="confirmRestore" />
          <UButton color="error" label="Delete" variant="ghost" @click="confirmDelete" />
        </div>
      </div>
    </template>
  </CrmDetailLayout>
</template>
