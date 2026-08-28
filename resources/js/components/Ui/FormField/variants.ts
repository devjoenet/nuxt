import { cva, type VariantProps } from "class-variance-authority";

export const formControlVariants = cva("w-full !bg-transparent px-4 text-base leading-6 focus:outline-none disabled:cursor-not-allowed disabled:opacity-60", {
  variants: {
    variant: {
      filled: "rounded-xl pb-2 pt-6",
      outlined: "rounded-xl py-4",
    },
    size: {
      sm: "min-h-12",
      md: "min-h-14",
      lg: "min-h-16",
    },
    multiline: {
      true: "min-h-32 pb-3 pt-6",
      false: "",
    },
  },
  defaultVariants: { variant: "outlined", size: "md", multiline: false },
});

export type FormControlVariants = VariantProps<typeof formControlVariants>;
