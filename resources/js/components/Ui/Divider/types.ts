import type { ClassValue } from "clsx";

export interface DividerProps {
  label?: string;
  orientation?: "horizontal" | "vertical";
  decorative?: boolean;
  class?: ClassValue;
}

export interface DividerSlots {
  default?: () => unknown;
}
