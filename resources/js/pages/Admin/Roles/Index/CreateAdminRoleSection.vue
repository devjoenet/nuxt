<script lang="ts" setup>
import { Form } from "@inertiajs/vue3";
import FormInput from "@/components/FormInput.vue";
import { getNestedFieldError } from "@/lib/formErrors";
import { store } from "@/routes/admin/roles";
import RolePermissionGroupList from "./RolePermissionGroupList.vue";
import type { AdminPermissionGroup } from "@/types";
import { ref } from "vue";

defineProps<{
  permissionGroups: AdminPermissionGroup[];
}>();

const open = ref(false);
</script>

<template>
  <UModal
    v-model:open="open"
    :ui="{
      content: 'w-[calc(100%-2rem)] sm:max-w-5xl',
      body: 'min-h-0',
      description: 'text-base sm:text-sm',
    }"
    description="Name the role and choose the capabilities it should have from day one."
    scrollable
    title="Create a role"
  >
    <UButton color="neutral" icon="i-ph-plus" label="New role" size="sm" variant="outline" />

    <template #body>
      <Form #default="{ errors, processing, recentlySuccessful }" class="grid gap-6" reset-on-success v-bind="store.form()">
        <FormInput :error="errors.name" label="Role name" name="name" required />

        <UFormField :error="getNestedFieldError(errors, 'permission_ids')" :ui="{ description: 'text-base sm:text-sm', label: 'text-base sm:text-sm' }" description="Select access by application area. You can change it later." label="Permission catalog" name="permission_ids">
          <RolePermissionGroupList :groups="permissionGroups" id-prefix="create-role-permission" test-id="create-role-permission-list" />
        </UFormField>

        <div class="flex flex-col gap-3 border-t border-default pt-5 sm:flex-row sm:items-center sm:justify-between">
          <p v-if="recentlySuccessful" aria-live="polite" class="text-base text-success-800 sm:text-sm dark:text-success-200" role="status">Role created.</p>

          <div class="grid gap-3 sm:ml-auto sm:flex sm:items-center">
            <UButton :label="recentlySuccessful ? 'Close' : 'Cancel'" class="w-full sm:w-fit" color="neutral" type="button" variant="outline" @click="open = false" />
            <UButton :disabled="processing" :loading="processing" class="w-full sm:w-fit" label="Create role" type="submit" />
          </div>
        </div>
      </Form>
    </template>
  </UModal>
</template>
