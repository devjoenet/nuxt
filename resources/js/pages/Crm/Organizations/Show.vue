<script lang="ts" setup>
  import { Link, router } from "@inertiajs/vue3";
  import Layout from "@/layouts/Default.vue";
  import { archive, destroy, edit, index, restore } from "@/routes/crm/organizations";
  import { show as contactShow } from "@/routes/crm/contacts";
  import type { Organization } from "@/types/crm";

  defineOptions({ layout: Layout });
  const props = defineProps<{ organization: Organization; canManage: boolean }>();
  const confirmArchive = () => {
    if (window.confirm("Archive this organization?")) router.post(archive(props.organization.id).url);
  };
  const confirmDelete = () => {
    if (window.confirm("Delete this organization? This removes it from active CRM results.")) router.delete(destroy(props.organization.id).url);
  };
</script>

<template>
  <UDashboardPanel id="crm-organization-detail">
    <template #header
      ><UDashboardNavbar :title="organization.name"
        ><template #leading><UDashboardSidebarCollapse /></template
        ><template #right
          ><UButton :to="index().url" color="neutral" label="All organizations" variant="ghost" /><UButton
            v-if="canManage"
            :to="edit(organization.id).url"
            color="neutral"
            label="Edit"
            variant="outline" /></template></UDashboardNavbar
    ></template>
    <template #body>
      <div class="grid gap-8 xl:grid-cols-[2fr_1fr]">
        <section class="grid gap-6">
          <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
              <h1 class="text-2xl font-semibold tracking-tight text-highlighted">{{ organization.name }}</h1>
              <a v-if="organization.website" :href="organization.website" class="text-base text-muted hover:text-highlighted sm:text-sm" rel="noreferrer" target="_blank">{{
                organization.website
              }}</a>
              <p v-else class="text-base text-muted sm:text-sm">No website recorded.</p>
            </div>
            <div v-if="canManage" class="flex gap-2">
              <UButton v-if="!organization.archived_at" color="error" label="Archive" variant="soft" @click="confirmArchive" /><UButton
                v-else
                :to="restore(organization.id).url"
                color="neutral"
                label="Restore"
                variant="outline"
              /><UButton color="error" label="Delete" variant="ghost" @click="confirmDelete" />
            </div>
          </div>
          <dl class="grid gap-4 border-y border-default py-5 sm:grid-cols-3">
            <div>
              <dt class="text-base text-muted sm:text-sm">Owner</dt>
              <dd class="font-medium text-highlighted">{{ organization.owner?.name ?? "Unassigned" }}</dd>
            </div>
            <div>
              <dt class="text-base text-muted sm:text-sm">Contacts</dt>
              <dd class="font-medium text-highlighted">{{ organization.contacts_count ?? organization.contacts?.length ?? 0 }}</dd>
            </div>
            <div>
              <dt class="text-base text-muted sm:text-sm">Leads</dt>
              <dd class="font-medium text-highlighted">{{ organization.leads_count ?? 0 }}</dd>
            </div>
          </dl>
          <section>
            <h2 class="text-xl font-semibold text-highlighted">Contacts</h2>
            <ul class="mt-4 divide-y divide-default" role="list">
              <li v-for="contact in organization.contacts" :key="contact.id" class="flex items-center justify-between gap-4 py-3">
                <div>
                  <Link :href="contactShow(contact.id).url" class="font-medium text-highlighted hover:underline">{{ contact.name }}</Link>
                  <p class="text-base text-muted sm:text-sm">{{ contact.email }} · {{ contact.phone ?? "No phone" }}</p>
                </div>
                <span class="text-base text-muted sm:text-sm">{{ contact.owner?.name ?? "Unassigned" }}</span>
              </li>
              <li v-if="!organization.contacts?.length" class="py-4 text-base text-muted sm:text-sm">No contacts recorded.</li>
            </ul>
          </section>
        </section>
      </div>
    </template>
  </UDashboardPanel>
</template>
