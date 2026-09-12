import { cn } from "@/lib/utils";

export const cardVariants = ["primary", "secondary", "muted", "neutral", "info", "success", "warning", "error"] as const;

export type CardVariant = (typeof cardVariants)[number];
export type CardStyle = "solid" | "outline" | "soft" | "subtle";
export type PageCardStyle = CardStyle | "ghost" | "naked";

const cardBorderClasses: Record<CardVariant, string> = {
  primary: "border-primary-200 dark:border-primary-700",
  secondary: "border-secondary-200 dark:border-secondary-700",
  muted: "border-slate-200 dark:border-slate-700",
  neutral: "border-grey-200 dark:border-grey-700",
  info: "border-info-200 dark:border-info-700",
  success: "border-success-200 dark:border-success-700",
  warning: "border-warning-200 dark:border-warning-700",
  error: "border-error-200 dark:border-error-700",
};

const cardBackgroundClasses: Record<CardVariant, string> = {
  primary: "bg-primary-100 dark:bg-primary-800",
  secondary: "bg-secondary-100 dark:bg-secondary-800",
  muted: "bg-slate-100 dark:bg-slate-800",
  neutral: "bg-grey-100 dark:bg-grey-800",
  info: "bg-info-100 dark:bg-info-800",
  success: "bg-success-100 dark:bg-success-800",
  warning: "bg-warning-100 dark:bg-warning-800",
  error: "bg-error-100 dark:bg-error-800",
};

const cardSolidClasses: Record<CardVariant, string> = {
  primary: "bg-primary-800 text-white dark:bg-primary-200 dark:text-primary-950",
  secondary: "bg-secondary-800 text-white dark:bg-secondary-200 dark:text-secondary-950",
  muted: "bg-slate-800 text-white dark:bg-slate-200 dark:text-slate-950",
  neutral: "bg-grey-800 text-white dark:bg-grey-200 dark:text-grey-950",
  info: "bg-info-800 text-white dark:bg-info-200 dark:text-info-950",
  success: "bg-success-800 text-white dark:bg-success-200 dark:text-success-950",
  warning: "bg-warning-800 text-white dark:bg-warning-200 dark:text-warning-950",
  error: "bg-error-800 text-white dark:bg-error-200 dark:text-error-950",
};

const cardHoverClasses: Record<CardVariant, string> = {
  primary: "hover:bg-primary-100 dark:hover:bg-primary-800",
  secondary: "hover:bg-secondary-100 dark:hover:bg-secondary-800",
  muted: "hover:bg-slate-100 dark:hover:bg-slate-800",
  neutral: "hover:bg-grey-100 dark:hover:bg-grey-800",
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
