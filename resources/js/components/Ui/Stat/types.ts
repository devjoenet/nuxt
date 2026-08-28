import type { ClassValue } from "clsx";
import type { UiTone } from "@/lib/uiTone";

export interface StatProps {
  label: string;
  value: string | number;
  description?: string;
  delta?: string;
  trend?: "up" | "down" | "flat";
  icon?: string;
  tone?: UiTone;
  class?: ClassValue;
}
