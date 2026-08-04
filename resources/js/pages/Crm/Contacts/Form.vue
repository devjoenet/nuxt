<script lang="ts" setup>
import { Form } from "@inertiajs/vue3";
import FormInput from "@/components/FormInput.vue";
import AsyncCrmSelect from "@/components/AsyncCrmSelect.vue";
import FormSelect from "@/components/FormSelect.vue";
import { index, show, store, update } from "@/routes/crm/contacts";
import { create as createOrganization } from "@/routes/crm/organizations";
import type { Contact, Option, Owner } from "@/types/crm";

const props = defineProps<{ contact: Contact | null; owners: Owner[]; canAssign: boolean }>();
const action = computed(() => (props.contact ? update(props.contact.id) : store()));
const ownerOptions = computed<Option[]>(() => props.owners.map((owner) => ({ label: owner.name, value: String(owner.id) })));
const organizationId = ref<string | null>(props.contact?.organization?.id ?? null);
</script>

<template>
  <UDashboardPanel id="crm-contact-form">
    <template #header
      ><UDashboardNavbar :title="contact ? 'Edit contact' : 'New contact'"
        ><template #leading><UDashboardSidebarCollapse /></template></UDashboardNavbar
    ></template>
    <template #body>
      <Form #default="{ errors, processing }" :action="action" class="mx-auto grid w-full max-w-3xl gap-6">
        <div>
          <h1 class="text-xl font-semibold text-highlighted">Contact details</h1>
          <p class="text-base text-muted sm:text-sm">Keep the relationship record useful without duplicating lead data.</p>
        </div>
        <div class="grid gap-4 sm:grid-cols-2">
          <FormInput :default-value="contact?.name" :error="errors.name" label="Name" name="name" required /><FormInput :default-value="contact?.email" :error="errors.email" label="Email" name="email" required type="email" /><FormInput
            :default-value="contact?.phone ?? undefined"
            :error="errors.phone"
            label="Phone"
            name="phone"
            type="tel"
          /><AsyncCrmSelect
            id="contact-organization"
            v-model="organizationId"
            :error="errors.organization_id"
            :initial-option="contact?.organization ? { label: contact.organization.name, value: contact.organization.id } : null"
            clearable
            label="Organization"
            name="organization_id"
            placeholder="Independent contact"
            type="organization"
          /><FormSelect v-if="canAssign" :default-value="contact?.owner ? String(contact.owner.id) : ''" :error="errors.owner_id" :items="ownerOptions" label="Owner" name="owner_id" placeholder="Unassigned" />
        </div>
        <div>
          <UButton :to="createOrganization().url" color="neutral" icon="i-ph-buildings" label="Create a new organization" variant="link" />
        </div>
        <div class="flex justify-end gap-3"><UButton :to="contact ? show(contact.id).url : index().url" color="neutral" label="Cancel" variant="ghost" /><UButton :label="contact ? 'Save changes' : 'Create contact'" :loading="processing" type="submit" /></div>
      </Form>
    </template>
  </UDashboardPanel>
</template>
