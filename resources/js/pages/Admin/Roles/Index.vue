<script lang="ts" setup>
import { Form, Head } from "@inertiajs/vue3";
import FormSelect from "@/components/FormSelect.vue";
import Layout from "@/layouts/Default.vue";
import { update } from "@/routes/admin/roles";
import type { AdminPermission, AdminRole } from "@/types";
import { computed, ref } from "vue";

defineOptions({ layout: Layout });

const props = defineProps<{ roles: AdminRole[]; permissions: AdminPermission[] }>();
const selectedRoleId = ref<number | null>(props.roles[0]?.id ?? null);
const selectedRole = computed(() => props.roles.find((role) => role.id === selectedRoleId.value));
const roleOptions = computed(() => props.roles.map((role) => ({ label: role.name, value: role.id })));
const isSaving = ref(false);

function beginSaving(): void {
  isSaving.value = true;
}

function finishSaving(): void {
  isSaving.value = false;
}

function permissionError(errors: Record<string, string>): string | undefined {
  return errors.permission_ids ?? Object.entries(errors).find(([key]) => key.startsWith("permission_ids."))?.[1];
}
</script>

<template>
  <Head title="Roles and permissions" />

  <UDashboardPanel id="admin-roles">
    <template #header>
      <UDashboardNavbar description="Manage capability access without exposing page-only permissions." title="Roles and permissions">
        <template #leading><UDashboardSidebarCollapse /></template>
      </UDashboardNavbar>
    </template>

    <template #body>
      <div class="grid gap-6">
        <UAlert v-if="!props.roles.length" class="w-fit max-w-full" color="neutral" description="There are no roles available to manage." icon="i-ph-info" title="No roles available" variant="subtle" />

        <FormSelect v-else id="role-selector" v-model="selectedRoleId" :disabled="isSaving" :items="roleOptions" class="role-selector w-full max-w-xl" label="Role" size="lg" />

        <UPageCard v-if="selectedRole" :key="selectedRole.id" :description="selectedRole.protected ? 'Protected system role' : 'Choose the capabilities this role can use.'" :title="selectedRole.name" class="w-full max-w-3xl" variant="subtle">
          <Form v-if="!selectedRole.protected" #default="{ errors, processing, recentlySuccessful }" class="grid gap-4" v-bind="update.form(selectedRole.id)" @finish="finishSaving" @start="beginSaving">
            <UFormField :error="permissionError(errors)" label="Permissions">
              <div v-if="props.permissions.length" class="grid grid-cols-1 gap-2 sm:grid-cols-2" data-testid="role-permission-list">
                <label v-for="permission in props.permissions" :key="permission.id" class="flex items-center gap-3 rounded-md px-2 py-1.5 hover:bg-elevated focus-within:bg-elevated">
                  <input :checked="selectedRole.permission_ids.includes(permission.id)" :value="permission.id" class="rounded border-default text-primary focus:ring-primary" name="permission_ids[]" type="checkbox" />
                  <span class="text-sm text-highlighted">{{ permission.label }}</span>
                </label>
              </div>
              <UAlert v-else class="w-fit max-w-full" color="neutral" description="No permissions are available in the current permission catalog." icon="i-ph-info" title="No permissions available" variant="subtle" />
            </UFormField>
            <UButton :disabled="processing" :loading="processing" class="w-fit" label="Save permissions" type="submit" />
            <p v-if="recentlySuccessful" aria-live="polite" class="text-sm text-success" role="status">Role permissions updated.</p>
          </Form>
          <UAlert v-else :ui="{ description: 'text-cyan-900 dark:text-cyan-100' }" class="w-fit max-w-full" color="info" description="All permissions are granted to Super Admin by the server-side Gate bypass. This role is not editable from the UI." icon="i-ph-info" variant="subtle" />
        </UPageCard>
      </div>
    </template>
  </UDashboardPanel>
</template>
