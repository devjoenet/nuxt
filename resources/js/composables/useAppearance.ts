import type { Appearance } from "@/types";
import { useColorMode } from "@vueuse/core";
import { computed, type ComputedRef } from "vue";

type ColorModePreference = "auto" | "light" | "dark";

interface AppearanceControls {
  appearance: ComputedRef<Appearance>;
  setAppearance: (appearance: Appearance) => void;
}

export function useAppearance(): AppearanceControls {
  const colorMode = useColorMode();
  const appearance = computed<Appearance>(() => (colorMode.store.value === "auto" ? "system" : colorMode.store.value));

  function setAppearance(nextAppearance: Appearance): void {
    const preference: ColorModePreference = nextAppearance === "system" ? "auto" : nextAppearance;
    const secure = window.location.protocol === "https:" ? "; Secure" : "";

    document.documentElement.dataset.appearance = nextAppearance;
    document.cookie = `appearance=${nextAppearance}; Path=/; Max-Age=31536000; SameSite=Lax${secure}`;
    colorMode.store.value = preference;
  }

  return { appearance, setAppearance };
}
