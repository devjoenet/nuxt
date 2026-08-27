import { cva, type VariantProps } from "class-variance-authority";
import { uiToneClasses } from "@/lib/uiTone";

export const alertVariants = cva("flex items-start gap-3 rounded-md border p-4 text-sm", {
  variants: {
    tone: uiToneClasses,
    variant: {
      soft: "border-[var(--ui-tone-border)] bg-[var(--ui-tone-soft)] text-[var(--ui-tone-strong)]",
      outline: "border-[var(--ui-tone-border)] bg-transparent text-[var(--ui-tone-strong)]",
      solid: "border-transparent bg-[var(--ui-tone)] text-[var(--ui-tone-contrast)] shadow-sm",
    },
  },
  defaultVariants: {
    tone: "info",
    variant: "soft",
  },
});

export type AlertVariants = VariantProps<typeof alertVariants>;
