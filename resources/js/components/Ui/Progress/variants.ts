import { cva, type VariantProps } from "class-variance-authority";
import { uiToneClasses } from "@/lib/uiTone";

export const progressVariants = cva("overflow-hidden rounded-md bg-surface-inset shadow-inner", {
  variants: {
    tone: uiToneClasses,
    size: {
      xs: "h-1",
      sm: "h-1.5",
      md: "h-2",
      lg: "h-5",
    },
  },
  defaultVariants: { tone: "primary", size: "md" },
});

export type ProgressVariants = VariantProps<typeof progressVariants>;
