import type { ClassValue } from "clsx";

export interface ListProps {
  ordered?: boolean;
  variant?: "plain" | "bullet" | "numbered" | "divided";
  spacing?: "compact" | "normal" | "relaxed";
  class?: ClassValue;
}

export interface ListSlots {
  default?: () => unknown;
}
