<script setup lang="ts">
import { Form, Link } from "@inertiajs/vue3";
import FormSelect from "@/components/FormSelect.vue";
import { cn } from "@/lib/utils";
import { index, resendVerification, resetTwoFactor, update } from "@/routes/admin/users";
import type { AdminRoleOption, AdminUsersPage } from "@/sections/admin/users/types";

const props = defineProps<{
  users: AdminUsersPage;
  roles: AdminRoleOption[];
  class?: string;
}>();

const statuses = [
  { label: "Active", value: "1" },
  { label: "Inactive", value: "0" },
];
</script>

<template>
  <UPageCard :class="cn(props.class)" description="Role and account status changes are recorded in the audit log." title="Workspace users" variant="subtle">
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="text-left text-xs text-muted">
          <tr>
            <th class="px-3 py-2 font-medium">User</th>
            <th class="px-3 py-2 font-medium">Role</th>
            <th class="px-3 py-2 font-medium">Status</th>
            <th class="px-3 py-2 text-right font-medium">Action</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-default">
          <tr v-for="user in users.data" :key="user.id">
            <td class="px-3 py-3">
              <p class="font-medium text-highlighted">{{ user.name }}</p>
              <p class="text-xs text-muted">{{ user.email }}</p>
            </td>
            <td class="px-3 py-3">
              <Form #default="{ processing }" class="flex items-center gap-2" v-bind="update.form(user.id)">
                <FormSelect :default-value="user.roles[0] ?? roles[0]?.value" :items="roles" class="min-w-36" label="Role" name="role" size="sm" />
                <FormSelect :default-value="user.is_active ? '1' : '0'" :items="statuses" class="min-w-28" label="Status" name="is_active" size="sm" />
                <UButton :loading="processing" label="Save" size="sm" type="submit" variant="soft" />
              </Form>
            </td>
            <td class="px-3 py-3">
              <UBadge :color="user.is_active ? 'success' : 'neutral'" variant="subtle">{{ user.is_active ? "Active" : "Inactive" }}</UBadge>
            </td>
            <td class="px-3 py-3 text-right">
              <div class="flex flex-wrap justify-end gap-2">
                <Form v-if="!user.email_verified_at" #default="{ errors, processing }" v-bind="resendVerification.form(user.id)">
                  <UButton :aria-busy="processing" :loading="processing" label="Resend verification" size="xs" type="submit" variant="ghost" />
                  <p v-if="errors.email" class="mt-1 text-xs text-error" role="alert">{{ errors.email }}</p>
                </Form>
                <Form v-if="user.two_factor_enabled" #default="{ errors, processing }" v-bind="resetTwoFactor.form(user.id)">
                  <UButton :aria-busy="processing" :data-testid="`reset-2fa-${user.id}`" :loading="processing" label="Reset 2FA" size="xs" type="submit" variant="ghost" />
                  <p v-if="errors.two_factor" class="mt-1 text-xs text-error" role="alert">{{ errors.two_factor }}</p>
                </Form>
              </div>
              <p class="mt-1 text-xs text-muted">{{ user.roles.join(", ") }}</p>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <nav v-if="users.links.length > 3" aria-label="Pagination" class="mt-4 flex flex-wrap gap-1">
      <Link v-for="link in users.links" :key="link.label" :class="['rounded-md px-3 py-1 text-sm', link.active ? 'bg-primary text-inverted' : 'text-muted hover:bg-elevated', !link.url && 'pointer-events-none opacity-50']" :href="link.url ?? index()">
        <span v-html="link.label" />
      </Link>
    </nav>
  </UPageCard>
</template>
