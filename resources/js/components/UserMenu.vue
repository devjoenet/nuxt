<script setup lang="ts">
import { getInitials } from "@/lib/nameFormatters";
import { useAppearance } from "@/composables/useAppearance";
import { logout } from "@/routes";
import { edit as profileEdit } from "@/routes/profile";
import { router } from "@inertiajs/vue3";
import type { DropdownMenuItem } from "@nuxt/ui";

defineProps<{
  collapsed?: boolean;
}>();

const { appearance, setAppearance } = useAppearance();
const auth = useAuth();

const user = computed(() => ({
  name: auth.value.user.name,
  avatar: {
    text: getInitials(auth.value.user.name),
  },
}));

const items = computed<DropdownMenuItem[][]>(() => [
  [
    {
      type: "label",
      label: user.value.name,
      avatar: user.value.avatar,
    },
  ],
  [
    {
      label: "Profile",
      icon: "i-ph-user",
      to: profileEdit.url(),
    },
    {
      label: "Settings",
      icon: "i-ph-gear-six",
      to: profileEdit.url(),
    },
  ],
  [
    {
      label: "Appearance",
      icon: "i-ph-circle-half-tilt",
      children: [
        {
          label: "System",
          icon: "i-ph-monitor",
          type: "checkbox",
          checked: appearance.value === "system",
          onSelect() {
            setAppearance("system");
          },
        },
        {
          label: "Light",
          icon: "i-ph-sun",
          type: "checkbox",
          checked: appearance.value === "light",
          onSelect() {
            setAppearance("light");
          },
        },
        {
          label: "Dark",
          icon: "i-ph-moon",
          type: "checkbox",
          checked: appearance.value === "dark",
          onSelect() {
            setAppearance("dark");
          },
        },
      ],
    },
  ],
  [
    {
      label: "Log out",
      icon: "i-ph-sign-out",
      to: logout(),
      onSelect: () => handleLogout(),
    },
  ],
]);

function handleLogout() {
  router.flushAll();
}
</script>

<template>
  <UDropdownMenu :items="items" :content="{ align: 'center', collisionPadding: 12 }" :ui="{ content: collapsed ? 'w-48' : 'w-(--reka-dropdown-menu-trigger-width)' }">
    <UButton
      v-bind="{
        ...user,
        label: collapsed ? undefined : auth.user.name,
        trailingIcon: collapsed ? undefined : 'i-ph-caret-up-down',
      }"
      color="neutral"
      variant="ghost"
      block
      :aria-label="`Open account menu for ${auth.user.name}`"
      data-testid="account-menu-trigger"
      :square="collapsed"
      class="data-[state=open]:bg-elevated"
      :ui="{
        trailingIcon: 'text-dimmed',
      }"
    />
  </UDropdownMenu>
</template>
