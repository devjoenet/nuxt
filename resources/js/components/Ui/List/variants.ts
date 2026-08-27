import { cva, type VariantProps } from "class-variance-authority";

export const listVariants = cva("text-content-body marker:font-semibold marker:text-content-subtle", {
  variants: {
    variant: {
      plain: "list-none",
      bullet: "list-disc ps-5",
      numbered: "list-decimal ps-5",
      divided: "list-none divide-y divide-content-divider [&>li]:py-3",
    },
    spacing: {
      compact: "space-y-1",
      normal: "space-y-2",
      relaxed: "space-y-4",
    },
  },
  compoundVariants: [{ variant: "divided", spacing: ["compact", "normal", "relaxed"], class: "space-y-0" }],
  defaultVariants: { variant: "plain", spacing: "normal" },
});

export type ListVariants = VariantProps<typeof listVariants>;
