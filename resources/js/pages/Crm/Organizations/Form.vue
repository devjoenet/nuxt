<script lang="ts" setup>
  import { Form } from "@inertiajs/vue3";
  import FormInput from "@/components/FormInput.vue";
  import FormSelect from "@/components/FormSelect.vue";
  import Layout from "@/layouts/Default.vue";
  import { index, show, store, update } from "@/routes/crm/organizations";
  import type { Option, Organization, Owner } from "@/types/crm";

  defineOptions({ layout: Layout });
  const props = defineProps<{ organization: Organization | null; owners: Owner[]; canAssign: boolean }>();
  const action = computed(() => (props.organization ? update(props.organization.id) : store()));
  const ownerOptions = computed<Option[]>(() => props.owners.map((owner) => ({ label: owner.name, value: String(owner.id) })));
</script>

<template>
  <UDashboardPanel id="crm-organization-form">
    <template #header
      ><UDashboardNavbar :title="organization ? 'Edit organization' : 'New organization'"
        ><template #leading><UDashboardSidebarCollapse /></template></UDashboardNavbar
    ></template>
    <template #body>
      <Form #default="{ errors, processing }" :action="action" class="mx-auto grid w-full max-w-3xl gap-6">
        <div>
          <h1 class="text-xl font-semibold text-highlighted">Organization details</h1>
          <p class="text-base text-muted sm:text-sm">Keep account context easy to find for the next person.</p>
        </div>
        <div class="grid gap-4 sm:grid-cols-2">
          <FormInput :default-value="organization?.name" :error="errors.name" class="sm:col-span-2" label="Organization name" name="name" required />
          <FormInput :default-value="organization?.website ?? undefined" :error="errors.website" label="Website" name="website" type="url" />
          <FormSelect
            v-if="canAssign"
            :default-value="organization?.owner ? String(organization.owner.id) : ''"
            :error="errors.owner_id"
            :items="ownerOptions"
            label="Owner"
            name="owner_id"
            placeholder="Unassigned"
          />
        </div>
        <div class="flex justify-end gap-3">
          <UButton :to="organization ? show(organization.id).url : index().url" color="neutral" label="Cancel" variant="ghost" /><UButton
            :label="organization ? 'Save changes' : 'Create organization'"
            :loading="processing"
            type="submit"
          />
        </div>
      </Form>
    </template>
  </UDashboardPanel>
</template>
