<script lang="ts" setup>
import AdminUsersTableSection from "@/sections/Admin/Users/AdminUsersTableSection.vue";
import InviteAdminUserSection from "@/sections/Admin/Users/InviteAdminUserSection.vue";
import type { AdminRoleOption, AdminUsersPage } from "@/sections/Admin/Users/types";

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
