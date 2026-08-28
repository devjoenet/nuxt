import { cva, type VariantProps } from "class-variance-authority";
import { uiToneClasses } from "@/lib/uiTone";

export const iconVariants = cva("inline-block shrink-0", {
  variants: {
    tone: { ...uiToneClasses, current: "text-current" },
    size: {
      xs: "size-3",
      sm: "size-4",
      md: "size-5",
      lg: "size-6",
      xl: "size-8",
    },
  },
  compoundVariants: [{ tone: ["primary", "secondary", "success", "info", "warning", "error", "neutral"], class: "text-[var(--ui-tone)]" }],
  defaultVariants: {
    tone: "current",
    size: "md",
  },
});

export type IconVariants = VariantProps<typeof iconVariants>;
