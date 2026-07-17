<script setup lang="ts">
  import { Motion } from "motion-v";

  const permissions = ["View dashboard", "Manage orders", "Edit rates", "Approve invoices", "View reports", "Manage users"];
  const roles = ["Admin", "Ops lead", "Analyst", "Driver"];
</script>

<template>
  <Motion
    as="article"
    :initial="{ opacity: 0, y: 18, rotate: -1 }"
    :transition="{ duration: 0.75, ease: 'easeOut' }"
    :viewport="{ once: true, amount: 0.2 }"
    :while-in-view="{ opacity: 1, y: 0, rotate: 0 }"
    class="rounded-xl border border-accent-500/25 bg-muted-50/82 p-4 shadow-2xl shadow-muted-950/12 ring-1 ring-muted-200/80 backdrop-blur-md dark:bg-muted-950/72 dark:shadow-muted-950/40 dark:ring-neutral-100/10"
    aria-label="User and role permissions preview"
  >
    <div class="flex items-center justify-between border-b border-muted-200 pb-4 dark:border-neutral-100/10">
      <h2 class="text-sm font-semibold text-muted-900 dark:text-neutral-100">User & role permissions</h2>
      <span aria-hidden="true" class="text-muted-500 dark:text-neutral-500">></span>
    </div>

    <div class="mt-4 overflow-hidden rounded-lg border border-muted-200 dark:border-neutral-100/10">
      <table class="w-full text-left text-xs">
        <thead class="bg-muted-100 text-muted-600 dark:bg-neutral-100/5 dark:text-neutral-400">
          <tr>
            <th scope="col" class="px-3 py-3 font-medium">Permission</th>
            <th v-for="role in roles" :key="role" scope="col" class="px-3 py-3 text-center font-medium">{{ role }}</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-muted-200 dark:divide-neutral-100/10">
          <tr v-for="permission in permissions" :key="permission">
            <th class="px-3 py-3 font-medium text-muted-700 dark:text-neutral-300" scope="row">{{ permission }}</th>
            <td v-for="role in roles" :key="`${permission}-${role}`" class="px-3 py-3 text-center text-accent-400">
              <UIcon aria-label="Included" class="mx-auto size-4" name="i-ph-check" />
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <p class="mt-4 text-right text-xs text-muted-500 dark:text-neutral-500">16 of 24 permissions</p>
  </Motion>
</template>
