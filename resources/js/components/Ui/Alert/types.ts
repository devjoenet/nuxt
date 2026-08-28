import type { ClassValue } from "clsx";
import type { UiTone } from "@/lib/uiTone";

export interface AlertProps {
  title?: string;
  description?: string;
  tone?: UiTone;
  variant?: "soft" | "outline" | "solid";
  icon?: string;
  dismissible?: boolean;
  role?: "alert" | "status";
  class?: ClassValue;
}

export interface AlertSlots {
  default?: () => unknown;
  actions?: () => unknown;
}
