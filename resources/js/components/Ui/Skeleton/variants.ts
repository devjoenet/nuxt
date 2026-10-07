import { cva, type VariantProps } from "class-variance-authority";

export const skeletonVariants = cva("bg-surface-inset", {
  variants: {
    shape: { line: "h-4 w-full rounded-sm", block: "min-h-24 w-full rounded-lg", circle: "size-10 rounded-full" },
    animated: { true: "motion-safe:animate-app-pulse", false: "" },
  },
  defaultVariants: { shape: "line", animated: true },
});

export type SkeletonVariants = VariantProps<typeof skeletonVariants>;
