import { cva, type VariantProps } from "class-variance-authority";
import { uiToneClasses } from "@/lib/uiTone";

export const statVariants = cva("rounded-xl border border-content-border bg-surface-elevated p-5 shadow-md", {
  variants: { tone: uiToneClasses },
  defaultVariants: { tone: "primary" },
});

export type StatVariants = VariantProps<typeof statVariants>;
