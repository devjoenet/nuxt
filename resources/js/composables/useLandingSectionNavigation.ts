import { useWindowScroll } from "@vueuse/core";
import { computed } from "vue";

export interface LandingNavigationItem {
  id: string;
  label: string;
  href: `#${string}`;
}

const navigation: LandingNavigationItem[] = [
  { id: "reality", label: "Why custom", href: "#reality" },
  { id: "services", label: "Services", href: "#services" },
  { id: "process", label: "Process", href: "#process" },
  { id: "operations", label: "Workflows", href: "#operations" },
];

export function useLandingSectionNavigation() {
  const { y } = useWindowScroll();
  const hasGlassBackground = computed(() => y.value > 28);
  const activeSection = computed(() => {
    if (typeof document === "undefined") {
      return "";
    }

    const activationLine = y.value + 160;

    return (
      navigation
        .map((item) => ({ id: item.id, top: document.getElementById(item.id)?.offsetTop ?? Number.POSITIVE_INFINITY }))
        .filter((section) => section.top <= activationLine)
        .at(-1)?.id ?? ""
    );
  });

  return { navigation, activeSection, hasGlassBackground };
}
