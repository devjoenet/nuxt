import type { ClassValue } from "clsx";
import type { UiTone } from "@/lib/uiTone";

export interface BadgeProps {
  label?: string;
  tone?: UiTone;
  variant?: "solid" | "soft" | "outline";
  size?: "sm" | "md" | "lg";
  dot?: boolean;
  class?: ClassValue;
}

export interface BadgeSlots {
  default?: () => unknown;
}
