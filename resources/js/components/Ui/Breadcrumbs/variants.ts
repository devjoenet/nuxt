import { cva, type VariantProps } from "class-variance-authority";

export const breadcrumbsVariants = cva("flex min-w-0 flex-wrap items-center gap-2 text-sm", {
  variants: {
    density: {
      compact: "gap-1 text-xs",
      normal: "gap-2 text-sm",
    },
  },
  defaultVariants: { density: "normal" },
});

export type BreadcrumbsVariants = VariantProps<typeof breadcrumbsVariants>;
