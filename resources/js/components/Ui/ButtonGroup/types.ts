import type { ClassValue } from "clsx";

export interface ButtonGroupProps {
  label: string;
  orientation?: "horizontal" | "vertical";
  attached?: boolean;
  class?: ClassValue;
}

export interface ButtonGroupSlots {
  default?: () => unknown;
}
