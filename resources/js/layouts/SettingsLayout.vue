<script setup lang="ts">
import type { NavigationMenuItem } from "@nuxt/ui";
import { edit as profileEdit } from "@/routes/profile";
import { show as securityShow } from "@/routes/security";

defineProps<{ documentTitle: string }>();

const links = [
  [
    {
      label: "Profile",
      icon: "i-ph-user",
      to: profileEdit.url(),
      exact: true,
    },
    {
      label: "Security",
      icon: "i-ph-shield",
      to: securityShow.url(),
    },
  ],
] satisfies NavigationMenuItem[][];
</script>

<template>
  <WorkspacePage id="settings" :document-title="documentTitle" :ui="{ body: 'lg:py-12' }" content-class="lg:gap-12" content-width="narrow" title="Settings">
    <template #toolbar>
      <UNavigationMenu :items="links" class="-mx-1 flex-1" highlight />
    </template>

    <slot />
  </WorkspacePage>
</template>
