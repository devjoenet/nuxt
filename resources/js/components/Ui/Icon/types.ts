import type { ClassValue } from "clsx";
import type { UiTone } from "@/lib/uiTone";

export interface IconProps {
  name: string;
  label?: string;
  decorative?: boolean;
  tone?: UiTone | "current";
  size?: "xs" | "sm" | "md" | "lg" | "xl";
  class?: ClassValue;
}
