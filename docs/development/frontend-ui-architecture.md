# Frontend UI Architecture

This document defines the composition boundary for the Inertia/Vue client. The goal is small, readable single-file
components with explicit responsibilities. Nuxt UI remains the vendor foundation; this project does not maintain a
second generic component library beside it.

Responsibility beats line count. A long component with one coherent responsibility can stay intact, while a shorter
component that mixes route coordination, reusable behavior, and domain rendering should be split.

## Layer model

| Layer | Home | Responsibility |
| --- | --- | --- |
| Vendor foundation | Nuxt UI, Vue, Inertia, VueUse, and `vite.config.ts` | Accessible UI primitives, reactivity, routing, and centralized theme/component defaults. Use Nuxt UI directly when no product contract exists. |
| Product primitives | `resources/js/components` | Repeated Southeast Code controls or display contracts. Existing `FormField`, form controls, `AsyncCrmSelect`, and `CrmDetailLayout` are the starting point. |
| Workflow shells | `resources/js/layouts` | Persistent application, authentication, settings, or workspace frames that own navigation and stable page chrome. |
| Domain sections | `resources/js/sections/<domain>/<workflow>` | A meaningful domain or workflow responsibility within a page, including its local interaction state and supporting types. |
| Pages | `resources/js/pages` | Inertia route entries that accept typed server props, select a layout, set route-level metadata, and declaratively compose sections. |
| Composables | `resources/js/composables` | Reusable reactive behavior, lifecycle integration, or stateful browser concerns. Check VueUse before adding one. |
| Pure utilities | `resources/js/lib` | Deterministic, side-effect-free functions with no Vue lifecycle or Inertia dependency. `cn` is the current class-merging utility. |

`resources/js/types` holds contracts shared across domains. Types used by only one section family stay beside that
family. `resources/js/actions`, `resources/js/routes`, and `resources/js/wayfinder` are generated Wayfinder transport
code and must not be edited as composition layers. `resources/js/assets`, `app.ts`, and `ssr.ts` remain supporting
infrastructure rather than composition layers.

## Dependency direction

Dependencies point toward more general layers, never back toward route or workflow owners:

- Pages may compose layouts, sections, product primitives, composables, and pure utilities.
- Layouts may compose other layouts, product primitives, composables, and pure utilities; they do not import pages or
  domain sections.
- Domain sections may compose sections in the same workflow, product primitives, composables, and pure utilities; they
  do not import pages or layouts.
- Product primitives may compose other product primitives, composables, and pure utilities; they do not import
  sections, layouts, or pages.
- Composables may depend on other composables and pure utilities, but not rendering layers.
- Pure utilities do not depend on Vue, VueUse, Inertia, or any rendering layer.

Nuxt UI primitives may be used directly in pages, layouts, sections, and product primitives. A local component earns
its place by owning a product or workflow contract, not by changing the name of a `UButton`, `UCard`, or other vendor
primitive. The ESLint configuration rejects imports that reverse these layer boundaries.

## Extraction rules

Extract a product primitive when the same product-level contract is repeated, or when a stable interaction contract
needs focused behavior and tests. Extract a domain section when a page contains a meaningful workflow responsibility
that can own a clear input/output API, even if that section is currently used by only one page. Extract a composable
when reactive behavior is shared or obscures the rendering responsibility. Extract a pure utility when the logic is
stateless and useful outside a component lifecycle.

Keep one-off markup at its call site when extraction would only move template lines. Do not create pass-through
wrappers that mirror Nuxt UI props and slots without adding a Southeast Code contract. Do not combine unrelated
controls into a configurable mega-component merely to reduce file count.

Before extracting, check the existing form controls, `AsyncCrmSelect`, `CrmDetailLayout`, domain sections, VueUse, and
Nuxt UI. New dependencies and new top-level folders under `resources/js` require separate approval.

## State ownership and event boundaries

- Pages own route-level coordination and receive server state through typed Inertia props. They choose sections and
  pass only the data each section needs.
- Sections own workflow-specific form, filter, selection, loading, and feedback state when that state is not shared
  across routes.
- Product primitives own only the internal interaction state required by their public control contract. Persistent
  business state remains in the page or section that invokes the route.
- Composables expose named reactive state and commands. They must make lifecycle effects and cleanup visible in their
  API rather than performing surprising work on import.
- Pure utilities own no reactive or persistent state.

Children emit user intent such as `save`, `cancel`, or `update:modelValue`; parents decide what domain mutation or
navigation follows. Use `v-model` only for a genuine controlled-value contract. Backend calls use Wayfinder-generated
routes at the nearest page or domain-section boundary that understands the operation. Product primitives must not
hide route changes or server mutations.

## Props, events, slots, and styling

- Type props, emitted events, template refs, and slot props. Reuse shared domain types instead of restating large
  anonymous object shapes.
- Prefer a small, intention-revealing API. Avoid boolean combinations that create several unrelated component modes.
- Use props for data and state. Use named slots for structural variation or caller-owned actions; type slot props when
  they expose data back to the caller.
- Forward relevant attributes to the interactive or root element. When a component owns root classes, merge caller
  classes with `cn` rather than discarding them.
- Keep external spacing at the call site. A reusable component may own its internal layout, but not the margin that
  positions it inside a particular page.
- Preserve native and Nuxt UI accessibility behavior. Labels, descriptions, errors, focus behavior, and keyboard
  interaction are part of the component contract.

## Naming and typing

Names describe the product responsibility: `FormInput`, `CrmDetailLayout`, and `LeadNotesSection` are useful examples.
Domain page regions end in `Section`; persistent frames end in `Layout`; composables start with `use`. Avoid generic
names such as `BaseWrapper`, `UiButton`, or `CardComponent` that conceal whether the code owns a real product contract.

Vue code remains strict TypeScript. Avoid `any`, type page props at the route boundary, import Wayfinder route helpers
instead of hard-coding application URLs, and keep transport-specific response shapes out of product primitives.

## Testing and enforcement

- Pure utilities receive focused unit coverage for input/output behavior.
- Product primitives receive component or browser coverage when they own validation, accessibility, or interaction
  behavior; simple presentational composition does not need a snapshot for its own sake.
- Domain sections receive focused workflow coverage. Route responses, authorization, and mutations remain covered by
  Laravel feature tests; critical client interaction receives browser coverage.
- Pages receive route-level feature coverage and browser smoke coverage in proportion to risk.
- `tests/Feature/FrontendArchitectureContractTest.php` protects the approved source homes, existing frontend
  foundations, and centralized Nuxt UI configuration.
- ESLint enforces dependency direction. The normal typecheck, build, formatting, and focused Pest checks still apply.

No line-count rule is part of this standard. Review responsibility, state ownership, API clarity, reuse, and testability
instead. If a deliberately approved architecture change needs a new top-level source folder or dependency direction,
update this document and its guardrails in the same reviewed change.
