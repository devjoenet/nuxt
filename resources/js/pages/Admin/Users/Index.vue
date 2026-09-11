<script lang="ts" setup>
import AdminUsersTableSection from "@/pages/Admin/Users/Components/AdminUsersTableSection.vue";
import InviteAdminUserSection from "@/pages/Admin/Users/Components/InviteAdminUserSection.vue";
import type { AdminRoleOption, AdminUsersPage } from "@/pages/Admin/Users/Components/types";

defineProps<{
  users: AdminUsersPage;
  roles: AdminRoleOption[];
}>();
</script>

<template>
  <WorkspacePage id="admin-users" description="Invite staff, assign roles, and control access." document-title="Admin users" title="Users">
    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_22rem]">
      <AdminUsersTableSection :roles="roles" :users="users" />
      <InviteAdminUserSection :roles="roles" />
    </div>
  </WorkspacePage>
</template>
