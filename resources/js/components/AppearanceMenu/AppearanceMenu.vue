<script setup lang="ts">
import { useAppearance } from "@/composables/useAppearance";
import type { Appearance } from "@/types";
import type { DropdownMenuItem } from "@nuxt/ui";
import { computed } from "vue";

const { appearance, setAppearance } = useAppearance();

const appearances = [
  { label: "System", value: "system", icon: "i-ph-monitor" },
  { label: "Light", value: "light", icon: "i-ph-sun" },
  { label: "Dark", value: "dark", icon: "i-ph-moon" },
] satisfies { label: string; value: Appearance; icon: string }[];

const activeIcon = computed(() => appearances.find((item) => item.value === appearance.value)?.icon ?? "i-ph-circle-half-tilt");
const items = computed<DropdownMenuItem[][]>(() => [
  [{ type: "label", label: "Appearance" }],
  appearances.map((item) => ({
    ...item,
    type: "checkbox" as const,
    checked: appearance.value === item.value,
    onSelect(): void {
      setAppearance(item.value);
    },
  })),
]);
</script>

<template>
  <UDropdownMenu :items="items" :content="{ align: 'end', collisionPadding: 12 }">
    <UButton :icon="activeIcon" aria-label="Change appearance" color="neutral" data-testid="appearance-menu-trigger" square variant="ghost" />
  </UDropdownMenu>
</template>
