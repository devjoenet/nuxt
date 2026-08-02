<script lang="ts" setup>
import type { AdminPermissionGroup } from "@/types";

withDefaults(
  defineProps<{
    groups: AdminPermissionGroup[];
    idPrefix: string;
    selectedPermissionIds?: number[];
    testId: string;
  }>(),
  {
    selectedPermissionIds: () => [],
  },
);
</script>

<template>
  <div class="@container">
    <div v-if="groups.length" :data-testid="testId" class="grid items-start gap-3 @3xl:grid-cols-2">
      <UPageCard v-for="group in groups" :key="group.key" :data-testid="`${testId}-${group.key}`" :ui="{ container: 'p-0 sm:p-0' }" class="min-w-0 overflow-hidden dark:bg-default dark:ring-accented dark:shadow-none" variant="outline">
        <fieldset class="min-w-0">
          <legend class="sr-only">{{ group.label }}</legend>

          <UCollapsible :unmount-on-hide="false" default-open>
            <template #default="{ open }">
              <UButton :aria-label="`${open ? 'Collapse' : 'Expand'} ${group.label} permissions`" :data-testid="`${testId}-${group.key}-trigger`" :ui="{ base: 'rounded-none px-4 py-3 sm:px-5', label: 'w-full' }" block color="neutral" type="button" variant="ghost">
                <div class="flex min-w-0 flex-1 items-center justify-between gap-3 text-left">
                  <div class="min-w-0">
                    <p class="text-base font-semibold text-highlighted sm:text-sm">{{ group.label }}</p>
                    <p class="text-base text-muted sm:text-sm">{{ group.permissions.length }} {{ group.permissions.length === 1 ? "permission" : "permissions" }}</p>
                  </div>
                  <UIcon :class="['size-4 shrink-0 transition-transform', open && 'rotate-180']" name="i-ph-caret-down" />
                </div>
              </UButton>
            </template>

            <template #content>
              <div class="grid gap-1 border-t border-muted p-3 sm:p-4">
                <label v-for="permission in group.permissions" :key="permission.id" :for="`${idPrefix}-${permission.id}`" class="flex min-h-11 cursor-pointer items-center gap-3 rounded-md px-3 py-2.5 hover:bg-elevated focus-within:bg-elevated sm:min-h-0 sm:py-2">
                  <input
                    :id="`${idPrefix}-${permission.id}`"
                    :checked="selectedPermissionIds.includes(permission.id)"
                    :value="permission.id"
                    class="size-5 shrink-0 rounded border-default text-primary focus:ring-2 focus:ring-primary sm:size-4"
                    name="permission_ids[]"
                    type="checkbox"
                  />
                  <p class="min-w-0 text-base text-highlighted sm:text-sm">{{ permission.label }}</p>
                </label>
              </div>
            </template>
          </UCollapsible>
        </fieldset>
      </UPageCard>
    </div>

    <UAlert v-else class="w-fit max-w-full" color="neutral" description="No permissions are available in the current permission catalog." icon="i-ph-info" title="No permissions available" variant="subtle" />
  </div>
</template>
