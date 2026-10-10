<script lang="ts" setup>
import { index } from "@/routes/admin/users";
import AdminUsersTableSection from "@/pages/Admin/Users/Components/AdminUsersTableSection.vue";
import InviteAdminUserSection from "@/pages/Admin/Users/Components/InviteAdminUserSection.vue";
import type { AdminRoleOption, AdminUsersPage } from "@/pages/Admin/Users/Components/types";

defineProps<{
  filters: { status?: "pending" | "active" };
  hasUntrackedInvitations: boolean;
  users: AdminUsersPage;
  roles: AdminRoleOption[];
}>();
</script>

<template>
  <WorkspacePage id="admin-users" description="Invite staff, assign roles, and control access." document-title="Admin users" title="Users">
    <p v-if="filters.status === 'pending' && hasUntrackedInvitations" class="mb-4 text-sm text-muted">Older invitations have no recorded acceptance status. This list shows tracked pending invitations only; review All users for older invitations.</p>
    <div class="mb-4 flex flex-wrap items-center gap-3">
      <UButton :to="index().url" color="neutral" :variant="!filters.status ? 'solid' : 'outline'" label="All users" />
      <UButton :to="index({ query: { status: 'pending' } }).url" color="neutral" :variant="filters.status === 'pending' ? 'solid' : 'outline'" label="Pending invitations" />
      <UButton :to="index({ query: { status: 'active' } }).url" color="neutral" :variant="filters.status === 'active' ? 'solid' : 'outline'" label="Active users" />
    </div>
    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_22rem]">
      <AdminUsersTableSection :roles="roles" :users="users" />
      <InviteAdminUserSection :roles="roles" />
    </div>
  </WorkspacePage>
</template>
