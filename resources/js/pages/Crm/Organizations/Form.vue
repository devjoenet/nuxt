<script lang="ts" setup>
import { FormInput } from "@/components/Ui/FormInput";
import { FormSelect } from "@/components/Ui/FormSelect";
import { index, show, store, update } from "@/routes/crm/organizations";
import type { Option, Organization, Owner } from "@/types/crm";

const props = defineProps<{ organization: Organization | null; owners: Owner[]; canAssign: boolean }>();
const action = computed(() => (props.organization ? update(props.organization.id) : store()));
const cancelTo = computed(() => (props.organization ? show(props.organization.id).url : index().url));
const ownerOptions = computed<Option[]>(() => props.owners.map((owner) => ({ label: owner.name, value: String(owner.id) })));
</script>

<template>
  <WorkspaceFormPage
    id="crm-organization-form"
    :action="action"
    :cancel-to="cancelTo"
    :submit-label="organization ? 'Save changes' : 'Create organization'"
    :title="organization ? 'Edit organization' : 'New organization'"
    section-description="Keep account context easy to find for the next person."
    section-title="Organization details"
  >
    <template #default="{ errors }">
      <div class="grid gap-4 sm:grid-cols-2">
        <FormInput :default-value="organization?.name" :error="errors.name" class="sm:col-span-2" label="Organization name" name="name" required />
        <FormInput :default-value="organization?.website ?? undefined" :error="errors.website" label="Website" name="website" type="url" />
        <FormSelect v-if="canAssign" :default-value="organization?.owner ? String(organization.owner.id) : ''" :error="errors.owner_id" :items="ownerOptions" label="Owner" name="owner_id" placeholder="Unassigned" />
      </div>
    </template>
  </WorkspaceFormPage>
</template>
