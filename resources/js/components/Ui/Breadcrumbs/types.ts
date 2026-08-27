import type { ClassValue } from "clsx";

export interface BreadcrumbItem {
  label: string;
  href?: string;
  current?: boolean;
}

export interface BreadcrumbsProps {
  items: BreadcrumbItem[];
  label?: string;
  density?: "compact" | "normal";
  class?: ClassValue;
}

export interface BreadcrumbsSlots {
  item?: (props: { item: BreadcrumbItem }) => unknown;
}
