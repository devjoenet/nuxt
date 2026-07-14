<script lang="ts" setup>
  import { Form, Head, Link } from "@inertiajs/vue3";
  import Layout from "@/layouts/Default.vue";
  import { index, resendVerification, resetTwoFactor, store, update } from "@/routes/admin/users";
  import type { AdminUser } from "@/types";

  defineOptions({ layout: Layout });

  defineProps<{
    users: { data: AdminUser[]; links: { url: string | null; label: string; active: boolean }[] };
    roles: { label: string; value: string }[];
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
        <UPageCard description="Role and account status changes are recorded in the audit log." title="Workspace users" variant="subtle">
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
                      <USelect :default-value="user.roles[0] ?? roles[0]?.value" :items="roles" class="min-w-36" name="role" />
                      <USelect
                        :default-value="user.is_active ? '1' : '0'"
                        :items="[
                          { label: 'Active', value: '1' },
                          { label: 'Inactive', value: '0' },
                        ]"
                        class="min-w-28"
                        name="is_active"
                      />
                      <UButton :loading="processing" label="Save" size="sm" type="submit" variant="soft" />
                    </Form>
                  </td>
                  <td class="px-3 py-3">
                    <UBadge :color="user.is_active ? 'success' : 'neutral'" variant="subtle">{{ user.is_active ? "Active" : "Inactive" }}</UBadge>
                  </td>
                  <td class="px-3 py-3 text-right">
                    <div class="flex flex-wrap justify-end gap-2">
                      <Form v-if="!user.email_verified_at" v-bind="resendVerification.form(user.id)"
                        ><UButton label="Resend verification" size="xs" type="submit" variant="ghost"
                      /></Form>
                      <Form v-bind="resetTwoFactor.form(user.id)"><UButton label="Reset 2FA" size="xs" type="submit" variant="ghost" /></Form>
                    </div>
                    <p class="mt-1 text-xs text-muted">{{ user.roles.join(", ") }}</p>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <nav v-if="users.links.length > 3" aria-label="Pagination" class="mt-4 flex flex-wrap gap-1">
            <Link
              v-for="link in users.links"
              :key="link.label"
              :class="['rounded-md px-3 py-1 text-sm', link.active ? 'bg-primary text-inverted' : 'text-muted hover:bg-elevated', !link.url && 'pointer-events-none opacity-50']"
              :href="link.url ?? index()"
            >
              <span v-html="link.label" />
            </Link>
          </nav>
        </UPageCard>

        <UPageCard description="They will receive a secure password setup link." title="Invite a user" variant="subtle">
          <Form #default="{ errors, processing }" class="grid gap-4" reset-on-success v-bind="store.form()">
            <UFormField :error="errors.name" label="Name" name="name"><UInput class="w-full" name="name" /></UFormField>
            <UFormField :error="errors.email" label="Email" name="email"><UInput class="w-full" name="email" type="email" /></UFormField>
            <UFormField :error="errors.role" label="Role" name="role"><USelect :default-value="roles[0]?.value" :items="roles" class="w-full" name="role" /></UFormField>
            <UButton :loading="processing" class="w-fit" label="Send invitation" type="submit" />
          </Form>
        </UPageCard>
      </div>
    </template>
  </UDashboardPanel>
</template>
