import { cva, type VariantProps } from "class-variance-authority";
import { uiToneClasses } from "@/lib/uiTone";

export const cardVariants = cva("overflow-hidden rounded-xl transition duration-(--app-motion-base) ease-app-standard", {
  variants: {
    tone: uiToneClasses,
    variant: {
      elevated: "border border-transparent bg-surface-elevated shadow-md",
      outline: "border border-content-border bg-surface-elevated shadow-none",
      soft: "border border-[var(--ui-tone-border)] bg-[var(--ui-tone-soft)] shadow-none",
      glass: "border border-white/30 bg-surface-translucent shadow-lg backdrop-blur-app",
      naked: "rounded-none border-0 bg-transparent shadow-none",
    },
    padding: {
      none: "p-0",
      sm: "p-4",
      md: "p-5 sm:p-6",
      lg: "p-6 sm:p-8",
    },
    interactive: {
      true: "hover:shadow-lg motion-safe:hover:-translate-y-1",
      false: "",
    },
  },
  defaultVariants: {
    tone: "neutral",
    variant: "elevated",
    padding: "md",
    interactive: false,
  },
});

export type CardVariants = VariantProps<typeof cardVariants>;
