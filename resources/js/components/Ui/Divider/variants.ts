import { cva, type VariantProps } from "class-variance-authority";

export const dividerVariants = cva("shrink-0 border-content-divider", {
  variants: {
    orientation: {
      horizontal: "w-full border-t",
      vertical: "h-full min-h-6 border-s",
    },
  },
  defaultVariants: {
    orientation: "horizontal",
  },
});

export type DividerVariants = VariantProps<typeof dividerVariants>;
