<script lang="ts" setup>
  import { Form, Head } from "@inertiajs/vue3";
  import Layout from "@/layouts/Default.vue";
  import { update } from "@/routes/admin/roles";
  import type { AdminPermission, AdminRole } from "@/types";

  defineOptions({ layout: Layout });
  defineProps<{ roles: AdminRole[]; permissions: AdminPermission[] }>();
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
      <div class="grid gap-6 md:grid-cols-2">
        <UPageCard
          v-for="role in roles"
          :key="role.id"
          :description="role.protected ? 'Protected system role' : 'Choose the capabilities this role can use.'"
          :title="role.name"
          variant="subtle"
        >
          <Form v-if="!role.protected" #default="{ errors, processing }" class="grid gap-4" v-bind="update.form(role.id)">
            <UFormField :error="errors.permission_ids" label="Permissions">
              <div class="grid gap-2">
                <label v-for="permission in permissions" :key="permission.id" class="flex items-center gap-3 rounded-md px-2 py-1.5 hover:bg-elevated">
                  <input
                    :checked="role.permission_ids.includes(permission.id)"
                    :value="permission.id"
                    class="rounded border-default text-primary focus:ring-primary"
                    name="permission_ids[]"
                    type="checkbox"
                  />
                  <span class="text-sm text-highlighted">{{ permission.label }}</span>
                </label>
              </div>
            </UFormField>
            <UButton :loading="processing" class="w-fit" label="Save permissions" type="submit" />
          </Form>
          <div v-else class="rounded-lg border border-default bg-elevated/50 p-4 text-sm text-muted">
            All permissions are granted to Super Admin by the server-side Gate bypass. This role is not editable from the UI.
          </div>
        </UPageCard>
      </div>
    </template>
  </UDashboardPanel>
</template>
