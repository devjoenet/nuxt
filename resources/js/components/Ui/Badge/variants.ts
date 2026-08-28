import { cva, type VariantProps } from "class-variance-authority";
import { uiToneClasses } from "@/lib/uiTone";

export const badgeVariants = cva("inline-flex w-fit items-center gap-1.5 rounded-md border font-bold leading-none", {
  variants: {
    tone: uiToneClasses,
    variant: {
      solid: "border-transparent bg-[var(--ui-tone)] text-[var(--ui-tone-contrast)]",
      soft: "border-transparent bg-[var(--ui-tone-soft)] text-[var(--ui-tone-strong)]",
      outline: "border-[var(--ui-tone-border)] bg-transparent text-[var(--ui-tone-strong)]",
    },
    size: {
      sm: "px-2 py-1 text-[0.65rem]",
      md: "px-2.5 py-1.5 text-xs",
      lg: "px-3 py-2 text-sm",
    },
  },
  defaultVariants: {
    tone: "primary",
    variant: "soft",
    size: "md",
  },
});

export type BadgeVariants = VariantProps<typeof badgeVariants>;
