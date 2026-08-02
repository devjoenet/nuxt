<script lang="ts" setup>
import { Form, Head } from "@inertiajs/vue3";
import FormSelect from "@/components/FormSelect.vue";
import Layout from "@/layouts/Default.vue";
import { update } from "@/routes/admin/roles";
import CreateAdminRoleSection from "@/sections/admin/roles/CreateAdminRoleSection.vue";
import RolePermissionGroupList from "@/sections/admin/roles/RolePermissionGroupList.vue";
import type { AdminPermissionGroup, AdminRole } from "@/types";
import { computed, ref } from "vue";

defineOptions({ layout: Layout });

const props = defineProps<{ roles: AdminRole[]; permissionGroups: AdminPermissionGroup[] }>();
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
        <template #right><CreateAdminRoleSection :permission-groups="permissionGroups" /></template>
      </UDashboardNavbar>
    </template>

    <template #body>
      <div class="mx-auto grid w-full max-w-5xl content-start gap-6">
        <UAlert v-if="!props.roles.length" class="w-fit max-w-full" color="neutral" description="There are no roles available to manage. Create a role to get started." icon="i-ph-info" title="No roles available" variant="subtle" />

        <template v-else>
          <section aria-labelledby="role-selection-heading" class="grid gap-4 border-b border-default pb-6 md:grid-cols-[minmax(0,1fr)_minmax(16rem,22rem)] md:items-end">
            <div class="grid gap-1">
              <h2 id="role-selection-heading" class="text-lg font-semibold text-highlighted mb-4">Choose a role</h2>
              <p class="max-w-2xl text-base text-muted sm:text-sm italic mb-4">Select the role whose access you want to review or change.</p>
            </div>

            <FormSelect id="role-selector" v-model="selectedRoleId" :disabled="isSaving" :items="roleOptions" class="role-selector w-full" label="Role" size="lg" />
          </section>

          <UPageCard
            v-if="selectedRole"
            :key="selectedRole.id"
            :description="selectedRole.protected ? 'Protected system role' : 'Review access by area, then save your changes.'"
            :title="selectedRole.name"
            :ui="{ description: 'text-base sm:text-sm mb-4 ', title: 'text-xl  font-bold mb-4' }"
            class="w-full dark:bg-elevated dark:ring-accented dark:shadow-none"
            data-testid="role-editor-card"
            variant="outline"
          >
            <Form v-if="!selectedRole.protected" #default="{ errors, processing, recentlySuccessful }" class="grid gap-6" v-bind="update.form(selectedRole.id)" @finish="finishSaving" @start="beginSaving">
              <UFormField :error="permissionError(errors)" :ui="{ description: 'text-sm mb-4 italic', label: 'text-lg mb-4 font-bold' }" description="Permissions are grouped by the part of the application they control." label="Permission catalog" name="permission_ids">
                <RolePermissionGroupList :groups="permissionGroups" :selected-permission-ids="selectedRole.permission_ids" id-prefix="role-permission" test-id="role-permission-list" />
              </UFormField>

              <div class="flex flex-col gap-3 border-t border-default pt-5 sm:flex-row sm:items-center sm:justify-between">
                <p v-if="recentlySuccessful" aria-live="polite" class="text-base text-success-800 sm:text-sm dark:text-success-200" role="status">Role permissions updated.</p>
                <UButton :disabled="processing" :loading="processing" class="w-full sm:ml-auto sm:w-fit" label="Save permissions" type="submit" />
              </div>
            </Form>
            <UAlert
              v-else
              :ui="{ description: 'text-cyan-900 dark:text-cyan-100' }"
              class="w-fit max-w-full"
              color="info"
              description="All permissions are granted to Super Admin by the server-side Gate bypass. This role is not editable from the UI."
              icon="i-ph-info"
              variant="subtle"
            />
          </UPageCard>
        </template>
      </div>
    </template>
  </UDashboardPanel>
</template>
