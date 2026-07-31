<script lang="ts" setup>
import { Head } from "@inertiajs/vue3";
import Layout from "@/layouts/Default.vue";
import AdminUsersTableSection from "@/sections/admin/users/AdminUsersTableSection.vue";
import InviteAdminUserSection from "@/sections/admin/users/InviteAdminUserSection.vue";
import type { AdminRoleOption, AdminUsersPage } from "@/sections/admin/users/types";

defineOptions({ layout: Layout });
defineProps<{
  users: AdminUsersPage;
  roles: AdminRoleOption[];
}>();
</script>

<template>
  <Head title="Admin users" />

  <UDashboardPanel id="admin-users">
    <template #header>
      <UDashboardNavbar description="Invite staff, assign roles, and control access." title="Users">
        <template #leading><UDashboardSidebarCollapse /></template>
      </UDashboardNavbar>
    </template>

    <template #body>
      <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_22rem]">
        <AdminUsersTableSection :roles="roles" :users="users" />
        <InviteAdminUserSection :roles="roles" />
      </div>
    </template>
  </UDashboardPanel>
</template>
