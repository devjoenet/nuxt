<script setup lang="ts">
import { getInitials } from "@/lib/nameFormatters";
import { logout } from "@/routes";
import { edit as profileEdit } from "@/routes/profile";
import { router } from "@inertiajs/vue3";
import type { DropdownMenuItem } from "@nuxt/ui";
import { useColorMode } from "@vueuse/core";

defineProps<{
  collapsed?: boolean;
}>();

const colorMode = useColorMode();
const auth = useAuth();

type ColorModePreference = "auto" | "light" | "dark";

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
          checked: colorMode.store.value === "auto",
          onSelect(e: Event) {
            e.preventDefault();

            setColorMode("auto");
          },
        },
        {
          label: "Light",
          icon: "i-ph-sun",
          type: "checkbox",
          checked: colorMode.store.value === "light",
          onSelect(e: Event) {
            e.preventDefault();

            setColorMode("light");
          },
        },
        {
          label: "Dark",
          icon: "i-ph-moon",
          type: "checkbox",
          checked: colorMode.store.value === "dark",
          onUpdateChecked(checked: boolean) {
            if (checked) {
              setColorMode("dark");
            }
          },
          onSelect(e: Event) {
            e.preventDefault();
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

function setColorMode(preference: ColorModePreference): void {
  const appearance = preference === "auto" ? "system" : preference;
  const secure = window.location.protocol === "https:" ? "; Secure" : "";

  document.cookie = `appearance=${appearance}; Path=/; Max-Age=31536000; SameSite=Lax${secure}`;
  colorMode.store.value = preference;
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
