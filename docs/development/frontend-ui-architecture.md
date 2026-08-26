# Frontend UI Architecture

This document defines the approved target and transitional composition boundary for the Inertia/Vue client. The goal
is small, responsibility-focused components with explicit public APIs. Nuxt UI remains the accessible vendor
foundation; the first-party Argon layer supplies portable visual primitives and application components add only real
Southeast Code behavior or defaults.

Responsibility remains the primary design test. Script budgets are review triggers that expose mixed ownership early;
they are not permission to split coherent behavior into arbitrary fragments.

## Layer model

| Layer | Home | Responsibility |
| --- | --- | --- |
| Vendor foundation | Nuxt UI, Vue, Inertia, VueUse, Reka UI, Motion, Unovis, and `vite.config.ts` | Accessibility behavior, reactivity, routing, and centralized vendor configuration. Direct Nuxt UI composition remains valid when no first-party contract exists. |
| UI primitives | `resources/js/components/Ui/<Primitive>` | Portable Argon presentation and generic accessible behavior with typed Vue APIs, semantic tokens, and CVA variants. |
| Application components | `resources/js/components/<Component>` | Reusable Southeast Code defaults, terminology, authorization-aware presentation, route coordination, or other deliberate product behavior. |
| Layouts | `resources/js/layouts` | Persistent application, authentication, settings, gallery, and workspace shells. |
| Page-family components | `resources/js/pages/<Family>/Components` | Components and supporting types owned by one page family or workflow. |
| Pages | `resources/js/pages` | Inertia route entries that accept typed server props, choose layouts, set route metadata, and coordinate domain behavior. |
| Composables | `resources/js/composables` | Reusable reactive behavior and explicit lifecycle or browser concerns. Check VueUse before adding one. |
| Pure helpers | `resources/js/lib` | Deterministic, side-effect-free helpers with no Vue, VueUse, Inertia, rendering-layer, or generated-transport dependency. |

`resources/js/types` holds authored contracts shared across domains. `resources/js/actions`, `resources/js/routes`,
and `resources/js/wayfinder` are generated Wayfinder transport trees: never edit them by hand or treat them as
composition layers. `resources/js/assets`, `app.ts`, and `ssr.ts` remain supporting infrastructure.

The current flat files and grouped directories under `resources/js/components` are transitional application
components. Existing `FormField`, form controls, `AsyncCrmSelect`, and `CrmDetailLayout` remain the starting point;
they move only with the route-family slice that can verify their behavior. New reusable components use the directory
standard below instead of adding to the flat legacy shape.

## Component directories and public APIs

Every new UI primitive and reusable application component receives a PascalCase directory matching the component
name. Only files that own a responsibility are created:

| File | Responsibility |
| --- | --- |
| `<Name>.vue` | Template and minimal orchestration. Component-scoped CSS is reserved for behavior utilities and tokens cannot express. |
| `index.ts` | The only supported import boundary for callers. It exports the component and every supported public type. |
| `types.ts` | Props, emits, slot contracts, and component-local public types when extraction improves clarity. |
| `variants.ts` | CVA definitions and variant-derived types. |
| `use<Name>.ts` | Stateful component behavior that is clearer and independently testable outside the SFC. |
| `utils.ts` or `constants.ts` | Pure transforms or stable values owned only by that component. |

Callers import `@/components/Ui/Button` or `@/components/AppButton`; they do not import `Button.vue`, `types.ts`,
`variants.ts`, or other component internals. Empty standard files and one-line indirection with no public-boundary
responsibility are prohibited. External spacing stays at the call site, and root classes merge caller input with the
existing `cn` helper.

## Dependency direction

Dependencies point toward more general layers:

```text
pages -> layouts -> application components -> UI primitives -> pure helpers
  |          |                 |                    |
  +----------+-----------------+--------------------+--> composables and shared types as allowed
```

- Pages may import layouts, page-family components, application components, UI primitives, composables, shared
  types, pure helpers, and generated routes or actions.
- Layouts may import application components, UI primitives, composables, types, pure helpers, and generated routes;
  they do not import pages or page-owned components.
- Application components may import other application components, UI primitives, composables, types, pure helpers,
  and generated routes; they do not import pages, page-owned components, or layouts.
- UI primitives may import Vue, approved generic behavior foundations, other UI primitive public APIs, generic types,
  product-free composables, and pure helpers. They do not import Inertia, generated transport code, application
  components, layouts, pages, or legacy sections.
- Composables do not import rendering layers. An explicitly transport-aware composable may import Inertia or
  generated routes when that concern is visible in its API.
- Pure helpers do not import Vue, VueUse, Inertia, generated transport code, or rendering layers.

Nuxt UI may still be used directly in application layers for intentional one-off composition. A wrapper that merely
renames a Nuxt UI component or forwards all of its props and slots has no product contract and should not exist.

## Page colocation and legacy retirement

Page-only components and supporting modules live in the nearest `pages/<Family>/Components` directory. Promote one
to the application layer only after a real cross-family product contract exists. Pages retain route props, layout
selection, metadata, and coordination; child components emit intent while the page decides which navigation or
mutation follows.

The existing `resources/js/sections` tree is a frozen compatibility boundary. Architecture coverage records its exact
Task 3 baseline, so new files cannot extend it. Later route-family migrations move a section beside its owning page or
promote a proven reusable contract into `components`, then remove that exact legacy path from the baseline. There is
no bulk move that merely recreates `sections` under a different spelling.

## State, props, events, slots, and styling

- Route pages use `<script setup>` so mutable page state remains instance-scoped.
- Pages own route-level coordination; page-family components own one workflow concern; application components own
  reusable product behavior; UI primitives own only generic control state.
- Props, emits, template refs, and exposed slot props remain typed. Use `v-model` only for a genuine controlled-value
  contract.
- Children emit user intent such as `save`, `cancel`, or `update:modelValue`; the nearest domain-aware parent owns
  Wayfinder navigation and server mutations.
- Use named slots for caller-owned structure or actions, forward meaningful HTML attributes, preserve native and
  Nuxt UI accessibility behavior, and keep external spacing at the call site.
- Direct design values do not belong in Vue templates. UI primitives consume the semantic Argon tokens and supported
  Tailwind utilities established by the foundation layer.

## Naming, typing, and generated code

Architectural roots directly under `resources/js` stay lowercase. Vue feature and component directories use
PascalCase, as do `.vue` filenames. Authored TypeScript filenames use camelCase, except the required `index.ts` public
API. Static asset paths use lowercase kebab-case.

Vue code remains strict TypeScript. Avoid `any`, type route props at the page boundary, and keep transport response
shapes out of UI primitives. Generated `actions`, `routes`, and `wayfinder` retain their generator-owned names and are
excluded from authored naming and SFC checks. Frontend code imports their supported generated APIs but never edits
their files or reaches through them from the portable UI or pure-helper layers.

## SFC responsibility budgets

Import blocks, blank lines, and generated declarations are excluded from these `<script setup>` review budgets:

- UI primitive: 40 lines.
- Reusable application component, layout, or page-family component: 60 lines.
- Inertia page: 40 lines.

An over-budget component needs an exact-file exception with a reason explaining why extraction would make ownership
less clear. Directory-wide and wildcard exceptions are prohibited. Task 3 starts with four transitional exceptions:

| Exact file | Budget | Reason |
| --- | ---: | --- |
| `resources/js/components/FormCurrencyInput.vue` | 60 | It owns legacy currency masking and Nuxt UI input adaptation; the floating-form migration owns its typed primitive split. |
| `resources/js/components/FormPhoneInput.vue` | 60 | It owns legacy phone formatting and Nuxt UI input adaptation; the floating-form migration owns its typed primitive split. |
| `resources/js/components/UserMenu.vue` | 60 | It currently coordinates route-aware account navigation and appearance state; the layouts/navigation migration owns its split. |
| `resources/js/pages/Settings/Profile.vue` | 40 | It intentionally coordinates one shared profile form to prevent partial forms overwriting shared state; the settings migration owns its final page-family extraction. |

Exceptions are removed as soon as a responsible migration brings the file within budget. The automated list is
exact, so a new over-budget file cannot borrow one of these reasons.

## Testing and enforcement

- ESLint enforces dependency direction and rejects imports that bypass component-directory public APIs.
- `tests/Feature/FrontendArchitectureContractTest.php` enforces source homes, PascalCase/camelCase naming, component
  directory exports, forbidden deep imports, generated-code exclusions, the frozen legacy-section baseline, page
  colocation spelling, and exact-file SFC budget exceptions.
- Pure helpers receive focused unit coverage. Components receive component or browser coverage when they own
  interaction, accessibility, or validation behavior; presentational composition does not need snapshots by default.
- Route behavior, authorization, and mutations remain covered by Laravel feature tests, with browser coverage for
  critical client interaction.
- Formatting, lint, typecheck, client/SSR build, focused Pest checks, and the relevant browser workflows remain the
  merge gate.

New dependencies or new top-level folders under `resources/js` still require separate approval. If an approved
architecture change alters a dependency direction, component API, legacy exception, or source home, update this
document and its guardrails in the same reviewed change.
