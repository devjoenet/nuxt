import type { ClassValue } from "clsx";
import type { UiTone } from "@/lib/uiTone";

export interface CardProps {
  as?: "article" | "div" | "section";
  tone?: UiTone;
  variant?: "elevated" | "outline" | "soft" | "glass" | "naked";
  padding?: "none" | "sm" | "md" | "lg";
  interactive?: boolean;
  class?: ClassValue;
}

export interface CardSlots {
  default?: () => unknown;
  header?: () => unknown;
  footer?: () => unknown;
}
