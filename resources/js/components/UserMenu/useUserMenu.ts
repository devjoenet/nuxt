import { router } from "@inertiajs/vue3";
import type { DropdownMenuItem } from "@nuxt/ui";
import { computed } from "vue";
import { useAppearance } from "@/composables/useAppearance";
import { useAuth } from "@/composables/useAuth";
import { getInitials } from "@/lib/nameFormatters";
import { logout } from "@/routes";
import { edit as profileEdit } from "@/routes/profile";

export function useUserMenu() {
  const { appearance, setAppearance } = useAppearance();
  const auth = useAuth();
  const user = computed(() => ({
    name: auth.value.user.name,
    avatar: { text: getInitials(auth.value.user.name) },
  }));
  const appearanceItems = computed<DropdownMenuItem[]>(() =>
    (
      [
        ["System", "monitor", "system"],
        ["Light", "sun", "light"],
        ["Dark", "moon", "dark"],
      ] as const
    ).map(([label, icon, value]) => ({
      label,
      icon: `i-ph-${icon}`,
      type: "checkbox",
      checked: appearance.value === value,
      onSelect: () => setAppearance(value),
    })),
  );
  const items = computed<DropdownMenuItem[][]>(() => [
    [{ type: "label", label: user.value.name, avatar: user.value.avatar }],
    [
      { label: "Profile", icon: "i-ph-user", to: profileEdit.url() },
      { label: "Settings", icon: "i-ph-gear-six", to: profileEdit.url() },
    ],
    [{ label: "Appearance", icon: "i-ph-circle-half-tilt", children: appearanceItems.value }],
    [{ label: "Log out", icon: "i-ph-sign-out", to: logout(), onSelect: () => router.flushAll() }],
  ]);

  return { auth, items, user };
}
