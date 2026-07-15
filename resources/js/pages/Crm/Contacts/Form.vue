<script lang="ts" setup>
  import { Form, Link } from "@inertiajs/vue3";
  import FormInput from "@/components/FormInput.vue";
  import FormSelect from "@/components/FormSelect.vue";
  import Layout from "@/layouts/Default.vue";
  import { index, show, store, update } from "@/routes/crm/contacts";
  import type { Contact, Option, Organization, Owner } from "@/types/crm";

  defineOptions({ layout: Layout });
  const props = defineProps<{ contact: Contact | null; organizations: Organization[]; owners: Owner[]; canAssign: boolean }>();
  const action = computed(() => (props.contact ? update(props.contact.id) : store()));
  const organizationOptions = computed<Option[]>(() => [
    { label: "Independent contact", value: "" },
    ...props.organizations.map((organization) => ({ label: organization.name, value: String(organization.id) })),
  ]);
  const ownerOptions = computed<Option[]>(() => [{ label: "Unassigned", value: "" }, ...props.owners.map((owner) => ({ label: owner.name, value: String(owner.id) }))]);
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
          <FormInput :default-value="contact?.name" :error="errors.name" label="Name" name="name" required /><FormInput
            :default-value="contact?.email"
            :error="errors.email"
            label="Email"
            name="email"
            required
            type="email"
          /><FormInput :default-value="contact?.phone ?? undefined" :error="errors.phone" label="Phone" name="phone" type="tel" /><FormSelect
            :default-value="contact?.organization_id ? String(contact.organization_id) : ''"
            :error="errors.organization_id"
            :items="organizationOptions"
            label="Organization"
            name="organization_id"
          /><FormSelect
            v-if="canAssign"
            :default-value="contact?.owner ? String(contact.owner.id) : ''"
            :error="errors.owner_id"
            :items="ownerOptions"
            label="Owner"
            name="owner_id"
          />
        </div>
        <div class="flex justify-end gap-3">
          <UButton :as="Link" :href="contact ? show(contact.id) : index()" color="neutral" label="Cancel" variant="ghost" /><UButton
            :label="contact ? 'Save changes' : 'Create contact'"
            :loading="processing"
            type="submit"
          />
        </div>
      </Form>
    </template>
  </UDashboardPanel>
</template>
