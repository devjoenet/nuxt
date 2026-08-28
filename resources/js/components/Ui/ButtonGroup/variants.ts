import { cva, type VariantProps } from "class-variance-authority";

export const buttonGroupVariants = cva("inline-flex", {
  variants: {
    orientation: {
      horizontal: "flex-row items-center",
      vertical: "flex-col items-stretch",
    },
    attached: {
      true: "[&>*:not(:first-child)]:-ms-px [&>*:not(:first-child)]:rounded-s-none [&>*:not(:last-child)]:rounded-e-none",
      false: "gap-2",
    },
  },
  defaultVariants: {
    orientation: "horizontal",
    attached: false,
  },
});

export type ButtonGroupVariants = VariantProps<typeof buttonGroupVariants>;
