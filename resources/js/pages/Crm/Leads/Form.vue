<script lang="ts" setup>
  import { Form, Link } from "@inertiajs/vue3";
  import FormInput from "@/components/FormInput.vue";
  import FormSelect from "@/components/FormSelect.vue";
  import FormTextarea from "@/components/FormTextarea.vue";
  import Layout from "@/layouts/Default.vue";
  import { index, show, store, update } from "@/routes/crm/leads";
  import type { Lead, Option, Owner } from "@/types/crm";

  defineOptions({ layout: Layout });
  const props = defineProps<{ lead: Lead | null; statuses: Option[]; sources: Option[]; owners: Owner[]; canAssign: boolean }>();
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
          <FormInput :default-value="lead?.contact.name" :error="errors.contact_name" label="Contact name" name="contact_name" required />
          <FormInput :default-value="lead?.contact.email" :error="errors.contact_email" label="Contact email" name="contact_email" required type="email" />
          <FormInput :default-value="lead?.contact.phone ?? undefined" :error="errors.contact_phone" label="Phone" name="contact_phone" />
          <template v-if="!lead">
            <FormInput :error="errors.organization_name" label="Organization" name="organization_name" />
            <FormInput :error="errors.organization_website" label="Organization website" name="organization_website" type="url" />
          </template>
          <FormSelect :default-value="lead?.status ?? 'new'" :error="errors.status" :items="statuses" label="Status" name="status" required />
          <FormSelect :default-value="lead?.source ?? 'direct'" :error="errors.source" :items="sources" label="Source" name="source" required />
          <FormSelect v-if="canAssign" :default-value="lead?.owner ? String(lead.owner.id) : ''" :error="errors.owner_id" :items="ownerOptions" label="Owner" name="owner_id" />
          <FormInput :default-value="lead?.service_interest ?? undefined" :error="errors.service_interest" label="Service interest" name="service_interest" />
          <FormInput :default-value="lead?.budget_range ?? undefined" :error="errors.budget_range" label="Budget range" name="budget_range" />
          <FormInput :default-value="lead?.timeline ?? undefined" :error="errors.timeline" label="Timeline" name="timeline" />
        </div>
        <FormTextarea :default-value="lead?.project_details" :error="errors.project_details" :rows="6" label="Project details" name="project_details" required />
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
