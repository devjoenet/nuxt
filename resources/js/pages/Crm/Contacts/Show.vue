<script lang="ts" setup>
  import { Link, router } from "@inertiajs/vue3";
  import Layout from "@/layouts/Default.vue";
  import { archive, destroy, edit, index, restore } from "@/routes/crm/contacts";
  import { show as organizationShow } from "@/routes/crm/organizations";
  import type { Contact } from "@/types/crm";

  defineOptions({ layout: Layout });
  const props = defineProps<{ contact: Contact; canManage: boolean }>();
  const confirmArchive = () => {
    if (window.confirm("Archive this contact?")) router.post(archive(props.contact.id).url);
  };
  const confirmDelete = () => {
    if (window.confirm("Delete this contact? This removes it from active CRM results.")) router.delete(destroy(props.contact.id).url);
  };
</script>

<template>
  <UDashboardPanel id="crm-contact-detail">
    <template #header
      ><UDashboardNavbar :title="contact.name"
        ><template #leading><UDashboardSidebarCollapse /></template
        ><template #right
          ><UButton :to="index().url" color="neutral" label="All contacts" variant="ghost" /><UButton
            v-if="canManage"
            :to="edit(contact.id).url"
            color="neutral"
            label="Edit"
            variant="outline" /></template></UDashboardNavbar
    ></template>
    <template #body>
      <section class="grid max-w-4xl gap-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
          <div>
            <h1 class="text-2xl font-semibold tracking-tight text-highlighted">{{ contact.name }}</h1>
            <p class="text-base text-muted sm:text-sm">{{ contact.email }} · {{ contact.phone ?? "No phone recorded" }}</p>
            <Link
              v-if="contact.organization"
              :href="organizationShow(contact.organization.id).url"
              class="mt-1 inline-block text-base text-muted hover:text-highlighted hover:underline sm:text-sm"
              >{{ contact.organization.name }}</Link
            >
            <p v-else class="mt-1 text-base text-muted sm:text-sm">Independent contact</p>
          </div>
          <div v-if="canManage" class="flex gap-2">
            <UButton v-if="!contact.archived_at" color="error" label="Archive" variant="soft" @click="confirmArchive" /><UButton
              v-else
              :to="restore(contact.id).url"
              color="neutral"
              label="Restore"
              variant="outline"
            /><UButton color="error" label="Delete" variant="ghost" @click="confirmDelete" />
          </div>
        </div>
        <dl class="grid gap-4 border-y border-default py-5 sm:grid-cols-2">
          <div>
            <dt class="text-base text-muted sm:text-sm">Owner</dt>
            <dd class="font-medium text-highlighted">{{ contact.owner?.name ?? "Unassigned" }}</dd>
          </div>
          <div>
            <dt class="text-base text-muted sm:text-sm">Related leads</dt>
            <dd class="font-medium text-highlighted">{{ contact.leads_count ?? 0 }}</dd>
          </div>
        </dl>
      </section>
    </template>
  </UDashboardPanel>
</template>
