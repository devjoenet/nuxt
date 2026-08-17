import { cn } from "@/lib/utils";

export const cardVariants = ["primary", "secondary", "muted", "neutral", "info", "success", "warning", "error"] as const;

export type CardVariant = (typeof cardVariants)[number];
export type CardStyle = "solid" | "outline" | "soft" | "subtle";
export type PageCardStyle = CardStyle | "ghost" | "naked";

const cardBorderClasses: Record<CardVariant, string> = {
  primary: "border-primary-800 dark:border-primary-800",
  secondary: "border-secondary-800 dark:border-secondary-800",
  muted: "border-muted-800 dark:border-muted-800",
  neutral: "border-neutral-800 dark:border-neutral-800",
  info: "border-info-800 dark:border-info-800",
  success: "border-success-800 dark:border-success-800",
  warning: "border-warning-800 dark:border-warning-800",
  error: "border-error-800 dark:border-error-800",
};

const cardBackgroundClasses: Record<CardVariant, string> = {
  primary: "bg-primary-100 dark:bg-primary-900",
  secondary: "bg-secondary-100 dark:bg-secondary-900",
  muted: "bg-muted-100 dark:bg-muted-900",
  neutral: "bg-neutral-100 dark:bg-neutral-900",
  info: "bg-info-100 dark:bg-info-900",
  success: "bg-success-100 dark:bg-success-900",
  warning: "bg-warning-100 dark:bg-warning-900",
  error: "bg-error-100 dark:bg-error-900",
};

const cardSolidClasses: Record<CardVariant, string> = {
  primary: "bg-primary-100 dark:bg-primary-900",
  secondary: "bg-secondary-100 dark:bg-secondary-900",
  muted: "bg-muted-100 dark:bg-muted-900",
  neutral: "bg-neutral-100 dark:bg-neutral-900",
  info: "bg-info-100 dark:bg-info-900",
  success: "bg-success-100 dark:bg-success-900",
  warning: "bg-warning-100 dark:bg-warning-900",
  error: "bg-error-100 dark:bg-error-900",
};

const cardHoverClasses: Record<CardVariant, string> = {
  primary: "hover:bg-primary-100 dark:hover:bg-primary-800",
  secondary: "hover:bg-secondary-100 dark:hover:bg-secondary-800",
  muted: "hover:bg-muted-100 dark:hover:bg-muted-800",
  neutral: "hover:bg-neutral-100 dark:hover:bg-neutral-800",
  info: "hover:bg-info-100 dark:hover:bg-info-800",
  success: "hover:bg-success-100 dark:hover:bg-success-800",
  warning: "hover:bg-warning-100 dark:hover:bg-warning-800",
  error: "hover:bg-error-100 dark:hover:bg-error-800",
};

export function cardAppearanceClass(style: PageCardStyle, variant: CardVariant): string {
  if (style === "solid") {
    return cardSolidClasses[variant];
  }

  if (style === "outline") {
    return cn("border bg-default ring-0", cardBorderClasses[variant]);
  }

  if (style === "soft") {
    return cn("ring-0", cardBackgroundClasses[variant]);
  }

  if (style === "subtle") {
    return cn("border ring-0", cardBackgroundClasses[variant], cardBorderClasses[variant]);
  }

  if (style === "ghost") {
    return cardHoverClasses[variant];
  }

  return "";
}
