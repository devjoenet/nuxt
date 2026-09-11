<script setup lang="ts">
import AppLogoIcon from "./AppLogoIcon.vue";

defineProps<{
  collapsed?: boolean;
}>();
</script>

<template>
  <div aria-label="Southeast Code workspace" class="flex min-w-0 items-center gap-2 rounded-lg px-2 py-2 text-highlighted" data-testid="workspace-identity" role="img">
    <AppLogoIcon aria-hidden="true" class="size-7 shrink-0 text-primary" />
    <span v-if="!collapsed" class="truncate text-sm font-semibold">Southeast Code</span>
  </div>
</template>
