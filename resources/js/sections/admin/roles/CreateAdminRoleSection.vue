<script lang="ts" setup>
import { Form } from "@inertiajs/vue3";
import FormInput from "@/components/FormInput.vue";
import { store } from "@/routes/admin/roles";
import type { AdminPermission } from "@/types";

defineProps<{
  permissions: AdminPermission[];
}>();

function permissionError(errors: Record<string, string>): string | undefined {
  return errors.permission_ids ?? Object.entries(errors).find(([key]) => key.startsWith("permission_ids."))?.[1];
}
</script>

<template>
  <UPageCard description="Name the role and choose its initial capabilities." title="Create a role" variant="subtle">
    <Form #default="{ errors, processing, recentlySuccessful }" class="grid gap-4" reset-on-success v-bind="store.form()">
      <FormInput :error="errors.name" label="Role name" name="name" required />

      <UFormField :error="permissionError(errors)" label="Permissions">
        <div v-if="permissions.length" class="grid grid-cols-1 gap-2" data-testid="create-role-permission-list">
          <label v-for="permission in permissions" :key="permission.id" class="flex items-center gap-3 rounded-md px-2 py-1.5 hover:bg-elevated focus-within:bg-elevated">
            <input :value="permission.id" class="rounded border-default text-primary focus:ring-primary" name="permission_ids[]" type="checkbox" />
            <span class="text-sm text-highlighted">{{ permission.label }}</span>
          </label>
        </div>
        <UAlert v-else class="w-fit max-w-full" color="neutral" description="No permissions are available in the current permission catalog." icon="i-ph-info" title="No permissions available" variant="subtle" />
      </UFormField>

      <UButton :disabled="processing" :loading="processing" class="w-fit" label="Create role" type="submit" />
      <p v-if="recentlySuccessful" aria-live="polite" class="text-sm text-success-800 dark:text-success-200" role="status">Role created.</p>
    </Form>
  </UPageCard>
</template>
