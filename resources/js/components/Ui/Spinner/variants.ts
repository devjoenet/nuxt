import { cva, type VariantProps } from "class-variance-authority";

export const spinnerVariants = cva("inline-block shrink-0 rounded-full border-2 border-current border-r-transparent motion-safe:animate-argon-spin", {
  variants: {
    size: {
      xs: "size-3",
      sm: "size-4",
      md: "size-5",
      lg: "size-6",
      xl: "size-8 border-3",
    },
  },
  defaultVariants: {
    size: "md",
  },
});

export type SpinnerVariants = VariantProps<typeof spinnerVariants>;
