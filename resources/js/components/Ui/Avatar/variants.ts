import { cva, type VariantProps } from "class-variance-authority";

export const avatarVariants = cva("relative inline-flex shrink-0 items-center justify-center overflow-visible bg-surface-inset font-semibold text-content-heading ring-2 ring-surface-elevated", {
  variants: {
    size: {
      xs: "size-6 text-[0.65rem]",
      sm: "size-8 text-xs",
      md: "size-10 text-sm",
      lg: "size-12 text-base",
      xl: "size-16 text-lg",
    },
    shape: {
      circle: "rounded-full",
      rounded: "rounded-lg",
    },
  },
  defaultVariants: {
    size: "md",
    shape: "circle",
  },
});

export type AvatarVariants = VariantProps<typeof avatarVariants>;
