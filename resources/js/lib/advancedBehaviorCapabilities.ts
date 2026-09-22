export type AdvancedBehaviorCapabilityId = "accordion" | "calendar" | "chart" | "date-time" | "dropdown" | "editor" | "kanban" | "map" | "modal" | "pagination" | "popover" | "select" | "table" | "tabs" | "toast" | "tooltip" | "upload";

export interface AdvancedBehaviorCapability {
  id: AdvancedBehaviorCapabilityId;
  implementation: string;
  lazy: boolean;
  manifestBehaviors: string[];
  status: "adapted" | "foundation";
}

export interface AdvancedDependencyEvaluation {
  accessibility: string;
  bundleImpact: string;
  license: string;
  maintenance: string;
  necessity: string;
  packageName: string;
  scope: "development" | "runtime";
  vueCompatibility: string;
}

export const advancedBehaviorCapabilities = [
  { id: "dropdown", implementation: "Nuxt UI UDropdownMenu on Reka UI", lazy: false, manifestBehaviors: ["bootstrap-dropdown"], status: "foundation" },
  { id: "tooltip", implementation: "Nuxt UI UTooltip on Reka UI", lazy: false, manifestBehaviors: ["bootstrap-tooltip"], status: "foundation" },
  { id: "popover", implementation: "Nuxt UI UPopover on Reka UI", lazy: false, manifestBehaviors: ["positioned-overlays"], status: "foundation" },
  { id: "accordion", implementation: "Nuxt UI UAccordion on Reka UI", lazy: false, manifestBehaviors: ["bootstrap-collapse"], status: "foundation" },
  { id: "tabs", implementation: "Nuxt UI UTabs on Reka UI", lazy: false, manifestBehaviors: ["bootstrap-tab"], status: "foundation" },
  { id: "modal", implementation: "Nuxt UI UModal on Reka UI", lazy: false, manifestBehaviors: ["bootstrap-modal"], status: "foundation" },
  { id: "toast", implementation: "Nuxt UI useToast through useFlashToasts", lazy: false, manifestBehaviors: ["inline-event-handlers"], status: "foundation" },
  { id: "table", implementation: "IndexTableFrame with Nuxt UI UTable", lazy: false, manifestBehaviors: ["data-tables"], status: "foundation" },
  { id: "pagination", implementation: "IndexPagination with Nuxt UI UPagination", lazy: false, manifestBehaviors: ["data-tables"], status: "foundation" },
  { id: "chart", implementation: "Lazy AdvancedChart adapter over Unovis", lazy: true, manifestBehaviors: ["charts"], status: "adapted" },
  { id: "date-time", implementation: "AdvancedDateTime over native date and time inputs", lazy: false, manifestBehaviors: ["date-and-time-pickers"], status: "adapted" },
  { id: "select", implementation: "FormSelect, FormMultiSelect, and FormAsyncSelect", lazy: false, manifestBehaviors: ["enhanced-selects"], status: "foundation" },
  { id: "upload", implementation: "FormFileUpload over Nuxt UI UFileUpload", lazy: false, manifestBehaviors: ["file-upload"], status: "foundation" },
  { id: "kanban", implementation: "AdvancedKanban with pointer and keyboard movement", lazy: false, manifestBehaviors: ["kanban"], status: "adapted" },
  { id: "calendar", implementation: "AdvancedCalendar with ISO-string boundary", lazy: false, manifestBehaviors: ["bootstrap-calendar", "calendar"], status: "adapted" },
  { id: "editor", implementation: "Lazy AdvancedEditor over Nuxt UI and TipTap", lazy: true, manifestBehaviors: ["rich-text-editing"], status: "adapted" },
  { id: "map", implementation: "Lazy AdvancedMap adapter over MapLibre GL", lazy: true, manifestBehaviors: ["maps"], status: "adapted" },
] satisfies AdvancedBehaviorCapability[];

export const advancedDependencyEvaluations = [
  {
    packageName: "vite-plus",
    scope: "development",
    necessity: "Aligns local dev and production builds with the current Laravel Vue starter toolchain.",
    license: "MIT",
    maintenance: "Active VoidZero project pinned to 0.3.0 for reproducible tooling.",
    accessibility: "Development-only tooling with no rendered interface.",
    bundleImpact: "Development dependency; application builds use its Vite-compatible core without shipping the CLI.",
    vueCompatibility: "Runs the existing Vue, Inertia, Laravel, Wayfinder, and Nuxt UI Vite plugins.",
  },
  {
    packageName: "@unovis/ts",
    scope: "runtime",
    necessity: "Declares the required peer for the existing @unovis/vue chart integration.",
    license: "Apache-2.0",
    maintenance: "Maintained alongside Unovis Vue 1.6.7.",
    accessibility: "AdvancedChart supplies an accessible label and a textual data table.",
    bundleImpact: "Loaded only through the lazy AdvancedChart boundary.",
    vueCompatibility: "Exact peer version for @unovis/vue 1.6.7.",
  },
  {
    packageName: "@internationalized/date",
    scope: "runtime",
    necessity: "Converts ISO dates at the AdvancedCalendar boundary without leaking vendor date objects.",
    license: "Apache-2.0",
    maintenance: "Actively maintained Adobe React Spectrum package already used by Nuxt UI.",
    accessibility: "Calendar keyboard and focus behavior remains owned by Nuxt UI and Reka UI.",
    bundleImpact: "Small date-model dependency shared with the existing Nuxt UI calendar implementation.",
    vueCompatibility: "Framework-neutral values accepted by Nuxt UI calendar components.",
  },
  {
    packageName: "maplibre-gl",
    scope: "runtime",
    necessity: "Replaces the manifest Leaflet map with a maintained, non-proprietary WebGL adapter.",
    license: "BSD-3-Clause",
    maintenance: "Active MapLibre community project at 6.6.0.",
    accessibility: "AdvancedMap adds labels, keyboard-enabled markers, status, errors, and a textual marker list.",
    bundleImpact: "Map runtime and CSS are dynamically imported and excluded from ordinary entry chunks.",
    vueCompatibility: "Imperative lifecycle is isolated behind a typed Vue mount and cleanup adapter.",
  },
] satisfies AdvancedDependencyEvaluation[];
