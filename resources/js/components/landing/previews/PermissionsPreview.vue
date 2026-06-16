<script setup lang="ts">
  import { Check } from "lucide-vue-next";

  const permissions = ["View dashboard", "Manage orders", "Edit rates", "Approve invoices", "View reports", "Manage users"];
  const roles = ["Admin", "Ops lead", "Analyst", "Driver"];
</script>

<template>
  <article
    class="rounded-xl border border-brand-primary-300/20 bg-muted-950/72 p-4 shadow-2xl shadow-muted-950/40 ring-1 ring-neutral-100/10 backdrop-blur-md"
    aria-label="User and role permissions preview"
  >
    <div class="flex items-center justify-between border-b border-neutral-100/10 pb-4">
      <h2 class="text-sm font-semibold text-neutral-100">User & role permissions</h2>
      <span class="text-neutral-500" aria-hidden="true">></span>
    </div>

    <div class="mt-4 overflow-hidden rounded-lg border border-neutral-100/10">
      <table class="w-full text-left text-xs">
        <thead class="bg-neutral-100/5 text-neutral-400">
          <tr>
            <th scope="col" class="px-3 py-3 font-medium">Permission</th>
            <th v-for="role in roles" :key="role" scope="col" class="px-3 py-3 text-center font-medium">{{ role }}</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-neutral-100/10">
          <tr v-for="permission in permissions" :key="permission">
            <th scope="row" class="px-3 py-3 font-medium text-neutral-300">{{ permission }}</th>
            <td v-for="role in roles" :key="`${permission}-${role}`" class="px-3 py-3 text-center text-brand-primary-300">
              <Check class="mx-auto size-4" aria-label="Included" />
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <p class="mt-4 text-right text-xs text-neutral-500">16 of 24 permissions</p>
  </article>
</template>
