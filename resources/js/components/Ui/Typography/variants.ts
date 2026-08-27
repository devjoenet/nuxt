import { cva, type VariantProps } from "class-variance-authority";

export const typographyVariants = cva("text-pretty", {
  variants: {
    variant: {
      display: "text-4xl leading-tight font-light tracking-tight sm:text-5xl lg:text-6xl",
      h1: "text-3xl leading-tight font-semibold sm:text-4xl lg:text-5xl",
      h2: "text-2xl leading-tight font-semibold sm:text-3xl lg:text-4xl",
      h3: "text-xl leading-snug font-semibold sm:text-2xl lg:text-3xl",
      h4: "text-lg leading-snug font-semibold sm:text-xl",
      body: "text-base leading-relaxed font-normal",
      lead: "text-lg leading-relaxed font-normal sm:text-xl",
      small: "text-sm leading-relaxed font-normal",
      caption: "text-xs leading-normal font-normal",
      eyebrow: "text-xs leading-normal font-bold tracking-wider uppercase",
    },
    tone: {
      heading: "text-content-heading",
      body: "text-content-body",
      subtle: "text-content-subtle",
      inverted: "text-white",
    },
  },
  defaultVariants: {
    variant: "body",
    tone: "body",
  },
});

export type TypographyVariants = VariantProps<typeof typographyVariants>;
