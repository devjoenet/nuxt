import type { ClassValue } from "clsx";
import type { UiTone } from "@/lib/uiTone";

export interface ProgressProps {
  value?: number;
  max?: number;
  label?: string;
  tone?: UiTone;
  size?: "xs" | "sm" | "md" | "lg";
  showValue?: boolean;
  class?: ClassValue;
}
