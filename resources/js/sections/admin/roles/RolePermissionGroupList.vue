<script lang="ts" setup>
import type { AdminPermissionGroup } from "@/types";

withDefaults(
  defineProps<{
    groups: AdminPermissionGroup[];
    selectedPermissionIds?: number[];
    testId: string;
  }>(),
  {
    selectedPermissionIds: () => [],
  },
);
</script>

<template>
  <div>
    <div v-if="groups.length" :data-testid="testId" class="grid gap-5">
      <fieldset v-for="group in groups" :key="group.key" class="min-w-0">
        <legend class="text-sm font-semibold text-highlighted">{{ group.label }}</legend>

        <div class="mt-2 grid grid-cols-[repeat(auto-fit,minmax(12rem,1fr))] gap-2">
          <label v-for="permission in group.permissions" :key="permission.id" class="flex items-center gap-3 rounded-md px-2 py-1.5 hover:bg-elevated focus-within:bg-elevated">
            <input :checked="selectedPermissionIds.includes(permission.id)" :value="permission.id" class="size-4 rounded border-default text-primary focus:ring-primary" name="permission_ids[]" type="checkbox" />
            <span class="text-sm text-highlighted">{{ permission.label }}</span>
          </label>
        </div>
      </fieldset>
    </div>

    <UAlert v-else class="w-fit max-w-full" color="neutral" description="No permissions are available in the current permission catalog." icon="i-ph-info" title="No permissions available" variant="subtle" />
  </div>
</template>
