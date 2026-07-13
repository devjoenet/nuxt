<script lang="ts" setup>
  import { Form, Link } from "@inertiajs/vue3";
  import Layout from "@/layouts/Default.vue";
  import { index, show, store, update } from "@/routes/crm/leads";
  import type { Lead, Option, Owner } from "@/types/crm";

  defineOptions({ layout: Layout });
  const props = defineProps<{ lead: Lead | null; statuses: Option[]; sources: Option[]; owners: Owner[] }>();
  const action = computed(() => (props.lead ? update(props.lead.id) : store()));
  const ownerOptions = computed(() => [{ label: "Unassigned", value: "" }, ...props.owners.map((owner) => ({ label: owner.name, value: String(owner.id) }))]);
</script>

<template>
  <UDashboardPanel id="crm-lead-form">
    <template #header
      ><UDashboardNavbar :title="lead ? 'Edit lead' : 'New lead'"
        ><template #leading><UDashboardSidebarCollapse /></template></UDashboardNavbar
    ></template>
    <template #body>
      <Form #default="{ errors, processing }" :action="action" class="mx-auto grid w-full max-w-3xl gap-6">
        <div>
          <h1 class="text-balance text-xl font-semibold text-highlighted">Contact and opportunity</h1>
          <p class="text-pretty text-base text-muted sm:text-sm">Keep the useful facts here. Archaeological records can wait.</p>
        </div>
        <div class="grid gap-4 sm:grid-cols-2">
          <UFormField :error="errors.contact_name" label="Contact name" name="contact_name" required
            ><UInput :default-value="lead?.contact.name" class="w-full" name="contact_name"
          /></UFormField>
          <UFormField :error="errors.contact_email" label="Contact email" name="contact_email" required
            ><UInput :default-value="lead?.contact.email" class="w-full" name="contact_email" type="email"
          /></UFormField>
          <UFormField :error="errors.contact_phone" label="Phone" name="contact_phone"
            ><UInput :default-value="lead?.contact.phone ?? undefined" class="w-full" name="contact_phone"
          /></UFormField>
          <template v-if="!lead"
            ><UFormField :error="errors.organization_name" label="Organization" name="organization_name"><UInput class="w-full" name="organization_name" /></UFormField
            ><UFormField :error="errors.organization_website" label="Organization website" name="organization_website"
              ><UInput class="w-full" name="organization_website" type="url" /></UFormField
          ></template>
          <UFormField :error="errors.status" label="Status" name="status" required
            ><USelect :default-value="lead?.status ?? 'new'" :items="statuses" class="w-full" name="status"
          /></UFormField>
          <UFormField :error="errors.source" label="Source" name="source" required
            ><USelect :default-value="lead?.source ?? 'direct'" :items="sources" class="w-full" name="source"
          /></UFormField>
          <UFormField :error="errors.owner_id" label="Owner" name="owner_id"
            ><USelect :default-value="lead?.owner ? String(lead.owner.id) : ''" :items="ownerOptions" class="w-full" name="owner_id"
          /></UFormField>
          <UFormField :error="errors.service_interest" label="Service interest" name="service_interest"
            ><UInput :default-value="lead?.service_interest ?? undefined" class="w-full" name="service_interest"
          /></UFormField>
          <UFormField :error="errors.budget_range" label="Budget range" name="budget_range"
            ><UInput :default-value="lead?.budget_range ?? undefined" class="w-full" name="budget_range"
          /></UFormField>
          <UFormField :error="errors.timeline" label="Timeline" name="timeline"><UInput :default-value="lead?.timeline ?? undefined" class="w-full" name="timeline" /></UFormField>
        </div>
        <UFormField :error="errors.project_details" label="Project details" name="project_details" required
          ><UTextarea :default-value="lead?.project_details" :rows="6" class="w-full" name="project_details"
        /></UFormField>
        <div class="flex justify-end gap-3">
          <UButton :as="Link" :href="lead ? show(lead.id) : index()" color="neutral" label="Cancel" variant="ghost" /><UButton
            :label="lead ? 'Save changes' : 'Create lead'"
            :loading="processing"
            type="submit"
          />
        </div>
      </Form>
    </template>
  </UDashboardPanel>
</template>
