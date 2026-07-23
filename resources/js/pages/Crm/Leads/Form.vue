<script lang="ts" setup>
  import { Form } from "@inertiajs/vue3";
  import AsyncCrmSelect from "@/components/AsyncCrmSelect.vue";
  import FormInput from "@/components/FormInput.vue";
  import FormMultiSelect from "@/components/FormMultiSelect.vue";
  import FormSelect from "@/components/FormSelect.vue";
  import FormTextarea from "@/components/FormTextarea.vue";
  import Layout from "@/layouts/Default.vue";
  import { create as createContact } from "@/routes/crm/contacts";
  import { create as createOrganization } from "@/routes/crm/organizations";
  import { index, show, store, update } from "@/routes/crm/leads";
  import type { Lead, Option, Owner } from "@/types/crm";

  defineOptions({ layout: Layout });
  const props = defineProps<{ lead: Lead | null; statuses: Option[]; sources: Option[]; owners: Owner[]; canAssign: boolean }>();
  const action = computed(() => (props.lead ? update(props.lead.id) : store()));
  const ownerOptions = computed(() => props.owners.map((owner) => ({ label: owner.name, value: String(owner.id) })));
  const contactId = ref<string | null>(props.lead?.contact?.id ?? null);
  const organizationId = ref<string | null>(props.lead?.organization?.id ?? null);
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
          <AsyncCrmSelect
            id="lead-contact"
            v-model="contactId"
            :error="errors.contact_id"
            :initial-option="lead?.contact ? { label: `${lead.contact.name} — ${lead.contact.email}`, value: lead.contact.id } : null"
            label="Contact"
            name="contact_id"
            placeholder="Search contacts"
            required
            type="contact"
          />
          <AsyncCrmSelect
            id="lead-organization"
            v-model="organizationId"
            :error="errors.organization_id"
            :initial-option="lead?.organization ? { label: lead.organization.name, value: lead.organization.id } : null"
            clearable
            label="Organization"
            name="organization_id"
            placeholder="Search organizations"
            type="organization"
          />
          <FormSelect :default-value="lead?.status ?? 'new'" :error="errors.status" :items="statuses" label="Status" name="status" required />
          <FormSelect :default-value="lead?.source ?? 'direct'" :error="errors.source" :items="sources" label="Source" name="source" required />
          <FormSelect
            v-if="canAssign"
            :default-value="lead?.owner ? String(lead.owner.id) : ''"
            :error="errors.owner_id"
            :items="ownerOptions"
            label="Owner"
            name="owner_id"
            placeholder="Unassigned"
          />
          <FormMultiSelect
            :default-value="lead?.service_interests ?? (lead?.service_interest ? [lead.service_interest] : [])"
            :error="errors.service_interest"
            :items="[
              { label: 'Operations application', value: 'operations-app' },
              { label: 'Dashboard or portal', value: 'dashboard-portal' },
              { label: 'System integration', value: 'integration' },
              { label: 'Reporting and visibility', value: 'reporting' },
              { label: 'Legacy modernization', value: 'modernization' },
              { label: 'Other', value: 'other' },
            ]"
            label="Service interests"
            name="service_interest"
          />
          <FormInput
            :default-value="lead?.budget_range ?? undefined"
            :error="errors.budget_range"
            inputmode="decimal"
            label="Estimated budget"
            min="0"
            name="budget_range"
            step="1000"
            type="number"
          >
            <template #leading><span class="text-muted">$</span></template>
          </FormInput>
          <FormInput :default-value="lead?.timeline ?? undefined" :error="errors.timeline" label="Timeline" name="timeline" />
        </div>
        <div class="flex flex-wrap gap-3 text-sm">
          <UButton :to="createContact().url" color="neutral" icon="i-ph-user-plus" label="Create a new contact" variant="link" />
          <UButton :to="createOrganization().url" color="neutral" icon="i-ph-buildings" label="Create a new organization" variant="link" />
        </div>
        <FormTextarea :default-value="lead?.project_details" :error="errors.project_details" :rows="6" label="Project details" name="project_details" required />
        <div class="flex justify-end gap-3">
          <UButton :to="lead ? show(lead.id).url : index().url" color="neutral" label="Cancel" variant="ghost" /><UButton
            :label="lead ? 'Save changes' : 'Create lead'"
            :loading="processing"
            type="submit"
          />
        </div>
      </Form>
    </template>
  </UDashboardPanel>
</template>
