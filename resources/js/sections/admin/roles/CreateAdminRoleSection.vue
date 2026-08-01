<script lang="ts" setup>
import { Form } from "@inertiajs/vue3";
import FormInput from "@/components/FormInput.vue";
import { store } from "@/routes/admin/roles";
import RolePermissionGroupList from "@/sections/admin/roles/RolePermissionGroupList.vue";
import type { AdminPermissionGroup } from "@/types";

defineProps<{
  permissionGroups: AdminPermissionGroup[];
}>();

function permissionError(errors: Record<string, string>): string | undefined {
  return errors.permission_ids ?? Object.entries(errors).find(([key]) => key.startsWith("permission_ids."))?.[1];
}
</script>

<template>
  <UPageCard description="Name the role and choose its initial capabilities." title="Create a role" variant="subtle">
    <Form #default="{ errors, processing, recentlySuccessful }" class="grid gap-4" reset-on-success v-bind="store.form()">
      <FormInput :error="errors.name" label="Role name" name="name" required />

      <UFormField :error="permissionError(errors)" label="Permissions" name="permission_ids">
        <RolePermissionGroupList :groups="permissionGroups" test-id="create-role-permission-list" />
      </UFormField>

      <UButton :disabled="processing" :loading="processing" class="w-fit" label="Create role" type="submit" />
      <p v-if="recentlySuccessful" aria-live="polite" class="text-sm text-success-800 dark:text-success-200" role="status">Role created.</p>
    </Form>
  </UPageCard>
</template>
