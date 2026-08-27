<script lang="ts" setup>
import { AsyncCrmSelect } from "@/components/AsyncCrmSelect";
import { FormInput } from "@/components/Ui/FormInput";
import { FormMultiSelect } from "@/components/Ui/FormMultiSelect";
import { FormSelect } from "@/components/Ui/FormSelect";
import { FormTextarea } from "@/components/Ui/FormTextarea";
import { create as createContact } from "@/routes/crm/contacts";
import { index, show, store, update } from "@/routes/crm/leads";
import { create as createOrganization } from "@/routes/crm/organizations";
import type { Lead, Option, Owner } from "@/types/crm";

const props = defineProps<{ lead: Lead | null; statuses: Option[]; sources: Option[]; serviceInterests: Option[]; owners: Owner[]; canAssign: boolean }>();
const action = computed(() => (props.lead ? update(props.lead.id) : store()));
const cancelTo = computed(() => (props.lead ? show(props.lead.id).url : index().url));
const ownerOptions = computed(() => props.owners.map((owner) => ({ label: owner.name, value: String(owner.id) })));
const contactId = ref<string | null>(props.lead?.contact?.id ?? null);
const organizationId = ref<string | null>(props.lead?.organization?.id ?? null);
</script>

<template>
  <WorkspaceFormPage
    id="crm-lead-form"
    :action="action"
    :cancel-to="cancelTo"
    :submit-label="lead ? 'Save changes' : 'Create lead'"
    :title="lead ? 'Edit lead' : 'New lead'"
    section-description="Keep the useful facts here. Archaeological records can wait."
    section-title="Contact and opportunity"
  >
    <template #default="{ errors }">
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
        <FormSelect v-if="canAssign" :default-value="lead?.owner ? String(lead.owner.id) : ''" :error="errors.owner_id" :items="ownerOptions" label="Owner" name="owner_id" placeholder="Unassigned" />
        <FormMultiSelect :default-value="lead?.service_interests ?? (lead?.service_interest ? [lead.service_interest] : [])" :error="errors.service_interest" :items="serviceInterests" label="Service interests" name="service_interest" />
        <FormInput :default-value="lead?.budget_range ?? undefined" :error="errors.budget_range" inputmode="decimal" label="Estimated budget" min="0" name="budget_range" step="1000" type="number">
          <template #leading><span class="text-muted">$</span></template>
        </FormInput>
        <FormInput :default-value="lead?.timeline ?? undefined" :error="errors.timeline" label="Timeline" name="timeline" />
      </div>
      <div class="flex flex-wrap gap-3 text-sm">
        <UButton :to="createContact().url" color="neutral" icon="i-ph-user-plus" label="Create a new contact" variant="link" />
        <UButton :to="createOrganization().url" color="neutral" icon="i-ph-buildings" label="Create a new organization" variant="link" />
      </div>
      <FormTextarea :default-value="lead?.project_details" :error="errors.project_details" :rows="6" label="Project details" name="project_details" required />
    </template>
  </WorkspaceFormPage>
</template>
