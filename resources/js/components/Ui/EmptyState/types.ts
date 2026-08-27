import type { ClassValue } from "clsx";

export interface EmptyStateProps {
  title: string;
  description?: string;
  icon?: string;
  compact?: boolean;
  class?: ClassValue;
}

export interface EmptyStateSlots {
  actions?: () => unknown;
}
