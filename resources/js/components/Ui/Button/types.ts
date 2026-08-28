import type { ClassValue } from "clsx";
import type { UiTone } from "@/lib/uiTone";

export type ButtonVariant = "solid" | "soft" | "outline" | "ghost" | "link";
export type ButtonSize = "xs" | "sm" | "md" | "lg";
export type ButtonShape = "rounded" | "pill" | "square";

export interface ButtonProps {
  label?: string;
  tone?: UiTone;
  variant?: ButtonVariant;
  size?: ButtonSize;
  shape?: ButtonShape;
  type?: "button" | "submit" | "reset";
  disabled?: boolean;
  loading?: boolean;
  block?: boolean;
  class?: ClassValue;
}

export interface ButtonSlots {
  default?: (props: { loading: boolean }) => unknown;
  leading?: (props: { loading: boolean }) => unknown;
  trailing?: (props: { loading: boolean }) => unknown;
}
