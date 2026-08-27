import type { ClassValue } from "clsx";

export type SpinnerSize = "xs" | "sm" | "md" | "lg" | "xl";

export interface SpinnerProps {
  label?: string;
  decorative?: boolean;
  size?: SpinnerSize;
  class?: ClassValue;
}
