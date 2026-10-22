<script lang="ts" setup>
import { ownerOptions as mapOwnerOptions, type OwnerOption } from "@southeastcode/crm/public";
import { AsyncCrmSelect } from "@southeastcode/crm/public";
import { FormInput } from "@/components/Ui/FormInput";
import { FormSelect } from "@/components/Ui/FormSelect";
import { index, show, store, update } from "@/routes/crm/contacts";
import { create as createOrganization } from "@/routes/crm/organizations";
import type { Contact, Option } from "@southeastcode/crm/public";

const props = defineProps<{ contact: Contact | null; owners: OwnerOption[]; canAssign: boolean }>();
const action = computed(() => (props.contact ? update(props.contact.id) : store()));
const cancelTo = computed(() => (props.contact ? show(props.contact.id).url : index().url));
const ownerOptions = computed<Option[]>(() => mapOwnerOptions(props.owners));
const organizationId = ref<string | null>(props.contact?.organization?.id ?? null);
</script>

<template>
  <WorkspaceFormPage
    id="crm-contact-form"
    :action="action"
    :cancel-to="cancelTo"
    :submit-label="contact ? 'Save changes' : 'Create contact'"
    :title="contact ? 'Edit contact' : 'New contact'"
    section-description="Keep the relationship record useful without duplicating lead data."
    section-title="Contact details"
  >
    <template #default="{ errors }">
      <div class="grid gap-4 sm:grid-cols-2">
        <FormInput :default-value="contact?.name" :error="errors.name" label="Name" name="name" required />
        <FormInput :default-value="contact?.email" :error="errors.email" label="Email" name="email" required type="email" />
        <FormInput :default-value="contact?.phone ?? undefined" :error="errors.phone" label="Phone" name="phone" type="tel" />
        <AsyncCrmSelect
          id="contact-organization"
          v-model="organizationId"
          :error="errors.organization_id"
          :initial-option="contact?.organization ? { label: contact.organization.name, value: contact.organization.id } : null"
          clearable
          label="Organization"
          name="organization_id"
          placeholder="Independent contact"
          type="organization"
        />
        <FormSelect v-if="canAssign" :default-value="contact?.owner ? String(contact.owner.id) : ''" :error="errors.owner_id" :items="ownerOptions" label="Owner" name="owner_id" placeholder="Unassigned" />
      </div>
      <div>
        <UButton :to="createOrganization().url" color="neutral" icon="i-ph-buildings" label="Create a new organization" variant="link" />
      </div>
    </template>
  </WorkspaceFormPage>
</template>
