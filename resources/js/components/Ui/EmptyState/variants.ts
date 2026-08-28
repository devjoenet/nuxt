import { cva, type VariantProps } from "class-variance-authority";

export const emptyStateVariants = cva("flex flex-col items-center justify-center text-center", {
  variants: {
    compact: { true: "gap-2 py-6", false: "gap-3 px-4 py-10 sm:px-8 sm:py-14" },
  },
  defaultVariants: { compact: false },
});

export type EmptyStateVariants = VariantProps<typeof emptyStateVariants>;
