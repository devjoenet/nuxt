import type { ClassValue } from "clsx";

export interface SkeletonProps {
  shape?: "line" | "block" | "circle";
  animated?: boolean;
  class?: ClassValue;
}
