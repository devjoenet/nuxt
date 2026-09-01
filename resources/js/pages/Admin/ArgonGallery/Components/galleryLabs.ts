import type { ArgonGalleryLabId } from "@/types/argonGallery";
import { defineAsyncComponent, type Component } from "vue";

export interface GalleryLabDefinition {
  component: Component;
  divergence: string;
  id: ArgonGalleryLabId;
  label: string;
  source: string;
}

export const galleryLabs = {
  foundations: {
    id: "foundations",
    label: "Foundations",
    component: defineAsyncComponent(() => import("./FoundationLab.vue")),
    source: "Authored semantic tokens in resources/css/theme/foundations.css.",
    divergence: "Licensed Sass values are translated into scoped semantic tokens; commercial Sass is not copied into the repository.",
  },
  primitives: {
    id: "primitives",
    label: "Primitives",
    component: defineAsyncComponent(() => import("./PrimitiveLab.vue")),
    source: "Typed components/Ui public APIs established by Argon Tasks 4–6.",
    divergence: "First-party Vue primitives replace Bootstrap markup while preserving the approved visual language.",
  },
  forms: {
    id: "forms",
    label: "Forms",
    component: defineAsyncComponent(() => import("./FormLab.vue")),
    source: "Typed floating-field primitives and browser-state contracts.",
    divergence: "Native and Nuxt UI controls replace kit plugins; autofill, readonly, loading, and validation states remain explicit.",
  },
  layouts: {
    id: "layouts",
    label: "Layouts",
    component: defineAsyncComponent(() => import("./LayoutLab.vue")),
    source: "Public, authentication, application, settings, and gallery shells from Task 7.",
    divergence: "The specimens are inert compositions so the gallery cannot trigger product workflows or authentication mutations.",
  },
  advanced: {
    id: "advanced",
    label: "Advanced",
    component: defineAsyncComponent(() => import("./AdvancedLab.vue")),
    source: "The Task 8 advanced capability registry and typed Vue adapters.",
    divergence: "Expensive chart, editor, and map runtimes retain a second lazy boundary inside the gallery-only lab chunk.",
  },
} satisfies Record<ArgonGalleryLabId, GalleryLabDefinition>;
