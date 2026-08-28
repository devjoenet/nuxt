import type { ClassValue } from "clsx";

export type TypographyVariant = "display" | "h1" | "h2" | "h3" | "h4" | "body" | "lead" | "small" | "caption" | "eyebrow";

export interface TypographyProps {
  as?: "h1" | "h2" | "h3" | "h4" | "h5" | "h6" | "p" | "span" | "div";
  variant?: TypographyVariant;
  tone?: "heading" | "body" | "subtle" | "inverted";
  class?: ClassValue;
}

export interface TypographySlots {
  default?: () => unknown;
}
