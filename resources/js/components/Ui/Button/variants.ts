import { cva, type VariantProps } from "class-variance-authority";
import { uiToneClasses } from "@/lib/uiTone";

export const buttonVariants = cva(
  "inline-flex items-center justify-center gap-2 border font-semibold tracking-[0.025rem] transition duration-(--argon-motion-base) ease-argon-standard select-none focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-content-focus disabled:pointer-events-none disabled:opacity-65 motion-safe:hover:-translate-y-px motion-safe:active:translate-y-0",
  {
    variants: {
      tone: uiToneClasses,
      variant: {
        solid: "border-transparent bg-[var(--ui-tone)] text-[var(--ui-tone-contrast)] shadow-sm hover:brightness-105",
        soft: "border-transparent bg-[var(--ui-tone-soft)] text-[var(--ui-tone-strong)] hover:bg-[var(--ui-tone-soft-hover)]",
        outline: "border-[var(--ui-tone-border)] bg-transparent text-[var(--ui-tone-strong)] hover:bg-[var(--ui-tone-soft)]",
        ghost: "border-transparent bg-transparent text-[var(--ui-tone-strong)] shadow-none hover:bg-[var(--ui-tone-soft)]",
        link: "border-transparent bg-transparent p-0 text-[var(--ui-tone-strong)] underline-offset-4 shadow-none hover:translate-y-0 hover:underline",
      },
      size: {
        xs: "min-h-8 px-3 text-xs",
        sm: "min-h-9 px-4 text-xs",
        md: "min-h-10 px-5 text-sm",
        lg: "min-h-12 px-6 text-sm",
      },
      shape: {
        rounded: "rounded-md",
        pill: "rounded-pill",
        square: "aspect-square rounded-md px-0",
      },
      block: {
        true: "w-full",
        false: "w-auto",
      },
    },
    compoundVariants: [{ variant: "link", size: ["xs", "sm", "md", "lg"], class: "min-h-0 px-0" }],
    defaultVariants: {
      tone: "primary",
      variant: "solid",
      size: "md",
      shape: "rounded",
      block: false,
    },
  },
);

export type ButtonVariants = VariantProps<typeof buttonVariants>;
