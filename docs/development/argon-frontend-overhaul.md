# Argon Frontend Overhaul Blueprint

## Purpose and status

This document is the approved implementation blueprint for replacing the current frontend with a Vue 3, Inertia 3,
Tailwind CSS 4 adaptation of Creative Tim's Argon Dashboard 3 Pro design system. It is a durable reference blueprint,
not an active task queue. The database-backed Development workspace remains the sole source of truth for task status,
assignment, acceptance criteria, implementation notes, testing rounds, and completion.

The work deliberately stops before extracting a reusable starter-kit repository. That later repository will be based on
the completed and hardened application implementation, not developed in parallel with it.

## Authorization and source material

The implementation is authorized under the team's Creative Tim Club membership and Access Pass. Source assets and
examples may be adapted into this application, but the licensed Argon source must not be published as a standalone,
unmodified distribution. Before a public starter kit is considered, the team must perform a separate license and asset
distribution review.

The reference sources have different jobs and should be consulted in this order:

1. `~/code/argon-dashboard-3-pro` is the canonical visual and behavioral implementation reference. Its HTML,
   Sass, JavaScript, assets, plugins, and page examples define the closest available source of truth.
2. [The Argon Dashboard 3 Pro landing dashboard](https://demos.creative-tim.com/argon-dashboard-pro/pages/dashboards/landing)
   is the rendered comparison target for responsive behavior, spacing, states, motion, and browser-specific details.
3. [Creative Tim's Argon documentation](https://www.creative-tim.com/learning-lab/bootstrap/overview/argon-dashboard)
   explains the intended component API and supported variants.
4. Existing application behavior, Laravel routes, permissions, Inertia props, and automated tests define the product
   requirements that the redesign must preserve.

When those sources disagree, preserve product behavior first, reproduce the licensed local kit's appearance and
interaction second, and document any deliberate divergence.

## Scope

The overhaul includes all of the following:

- Reproduce the Argon design language, visual tokens, components, states, responsive behavior, and interactions in the
  current Vue/Inertia/Tailwind stack.
- Build first-party Argon primitives rather than shipping Bootstrap-oriented markup or runtime dependencies.
- Provide floating-label versions of the application's form controls without losing current options, validation,
  accessibility, masking, formatting, or Inertia form integration.
- Rebuild every current application page without changing its business behavior, route contract, authorization, or
  server-side workflow.
- Make the licensed kit's example pages, components, sections, and supported functionality available as protected
  application resources for discovery and reuse.
- Remove the legacy frontend implementation only after its replacement has passed the appropriate feature, type,
  build, accessibility, responsive, visual, and browser checks.

The following are outside this program:

- Extracting or publishing the future starter-kit repository.
- Redesigning Laravel domain behavior merely because the frontend is changing.
- Replacing Inertia, Vue, Tailwind CSS, Wayfinder, Pest, or the existing Laravel application architecture.
- Adding a Bootstrap runtime to make the original HTML work unchanged.
- Copying plugin dependencies without evaluating their need, license, bundle cost, accessibility, and Vue suitability.

## Verified baseline

The counts below were taken at the start of Task 1 and form the migration denominator. Regenerate the inventory if
either codebase changes before its corresponding task begins.

| Inventory | Verified count |
| --- | ---: |
| Current Inertia Vue pages | 35 |
| Current files under `resources/js/components` | 57 |
| Current files under `resources/js/sections` | 34 |
| Non-vendor Laravel routes | 88 |
| Licensed Argon HTML examples | 69 |

The 69 licensed HTML examples are distributed as follows:

| Argon example family | Count |
| --- | ---: |
| Account | 4 |
| Applications | 5 |
| Authentication | 17 |
| Dashboards | 5 |
| Ecommerce | 8 |
| General pages | 22 |
| Projects | 3 |
| Team | 5 |
| Total | 69 |

The metadata-only [`source-manifest.json`](../../resources/argon/source-manifest.json) records those source files, their
assets, plugins, behaviors, provenance, distribution decisions, and planned Vue destinations. The manifest—not a
remembered list—drives gallery completeness checks. Its separate
[licensing and distribution review](argon-source-licensing.md) records the unresolved questions that must be answered
before any public starter-kit extraction.

### Task 1 baseline results

The following snapshot was recorded on August 18, 2026. It describes the inherited application before Argon
implementation and is intentionally not a claim that existing unrelated failures were repaired by this planning task.

| Check | Result |
| --- | --- |
| Documentation contract | Passed: 3 tests, 44 assertions |
| Frontend formatting | Initially failed on one blank line in `Admin/Roles/Index.vue`; mechanical Prettier correction applied, then passed |
| Frontend lint | Passed |
| Vue/TypeScript typecheck | Passed |
| Client and SSR production builds | Passed: 2,690 client modules and 468 SSR modules transformed |
| PHP type coverage | Passed at 100% |
| PHPStan | Passed at both configured levels with zero errors |
| PHP lint/format checks | Initially failed on `config/datalens.php`; mechanical Pint correction applied, then Pint, Rector, and Prettier passed |
| Unit and Feature tests with Herd coverage | 406 passed, 1 inherited card-surface contract failed |
| Browser tests | 52 passed, 3 inherited visual/accessibility contracts failed |

The inherited test failures share two existing presentation problems that remain outside this documentation branch:

- Neutral and role-editor card borders currently resolve to the `800` token in both themes, while their contract and
  browser expectations require lighter theme-specific borders.
- The Development roadmap summary has insufficient contrast for primary and info text/badge combinations.

The browser run saved diagnostic screenshots for the three failing scenarios. These failures must be reconciled with
the approved Argon token work rather than concealed by weakening the assertions.

## Non-negotiable migration constraints

1. Preserve named routes and use generated Wayfinder imports for frontend navigation and mutations.
2. Preserve Inertia page props, deferred-prop behavior, request validation, policy decisions, and permission boundaries.
3. Preserve keyboard operation, focus visibility, labels, descriptions, validation messages, reduced-motion behavior,
   semantic HTML, and readable contrast.
4. Preserve light and dark themes. Argon values become scoped application tokens rather than unscoped global patches.
5. Do not edit generated `resources/js/actions`, `resources/js/routes`, or `resources/js/wayfinder` files by hand.
6. Do not add a dependency when Vue, VueUse, Reka UI, Motion, Unovis, or a small first-party implementation already
   provides the required behavior. Any new dependency requires explicit review and approval.
7. Migrate a page or component as one complete vertical slice: implementation, tests, visual comparison, responsive
   checks, and removal of the superseded code.
8. Keep the application buildable at every merged task. Transitional adapters are allowed; two competing permanent
   design systems are not.
9. Do not encode live status in this document. Use the Development workspace for sequencing and progress.

## Target frontend organization

Architectural roots under `resources/js` remain lowercase to avoid case-only path churn on different filesystems and
to preserve the existing Vite and Inertia conventions. The `@` Vite alias and TypeScript path continue to resolve to
`resources/js`.

```text
resources/js/
├── actions/                 # Wayfinder-generated; never edited manually
├── assets/                  # Authored frontend assets
├── components/
│   ├── Ui/                  # Argon primitives, one PascalCase directory per primitive
│   │   └── FormInput/
│   │       ├── FormInput.vue
│   │       ├── index.ts
│   │       ├── types.ts
│   │       ├── variants.ts
│   │       └── useFormInput.ts
│   └── AppButton/           # Application-level wrappers and product contracts
│       ├── AppButton.vue
│       ├── index.ts
│       └── types.ts
├── composables/             # Shared reactive behavior; consult VueUse first
├── layouts/                 # Persistent route-family shells
├── lib/                     # Pure, side-effect-free helpers
├── pages/
│   └── Crm/
│       └── Leads/
│           ├── Index.vue
│           └── Components/  # Page-family-owned components
├── routes/                  # Wayfinder-generated; never edited manually
├── types/                   # Cross-domain authored contracts
└── wayfinder/               # Wayfinder-generated; never edited manually
```

### `components/Ui`

`resources/js/components/Ui` is the portable design-system layer. It owns the Argon visual vocabulary and generic
accessible behavior:
buttons, fields, cards, badges, avatars, alerts, dropdowns, navigation primitives, modals, tables, pagination, tabs,
progress, tooltips, loading states, and similar controls.

UI primitives must not import pages, layouts, application components, generated route functions, Inertia mutation
APIs, or domain types. A primitive may use Vue, Tailwind classes, Reka UI, VueUse, Motion, Unovis, and pure helpers.
The public API is exported from the component directory's `index.ts`; callers do not deep-import internal files.

### `components`

This is the reusable application layer. Components here extend or compose UI primitives and own a real application
contract: default props, product terminology, authorization-aware presentation, Inertia coordination, route-aware
navigation, analytics hooks, or other deliberate side effects. They may import UI primitives, composables, types,
pure helpers, and generated routes, but not pages or layouts.

A wrapper that merely renames a primitive or forwards all of its props has no reason to exist. Application defaults
that can be expressed in a primitive variant also do not justify another component.

### `pages`

Pages remain Inertia route entries and own route-level prop boundaries, metadata, layout selection, and domain
coordination. Components that serve a single page family live in that family's `Components` directory. This keeps
one-off workflow code near its owner without turning `components` into a miscellaneous drawer.

The existing `resources/js/sections` root will be removed progressively. A section moves beside its owning page when
that page is migrated, or into `components` when demonstrated reuse gives it an application-wide contract. There will
be no bulk relocation that preserves the current structure under a different spelling.

### Supporting roots

- `layouts` owns persistent application, authentication, settings, gallery, and workspace shells.
- `composables` owns reusable stateful or lifecycle-aware behavior. VueUse is checked before a custom composable is
  created.
- `lib` owns deterministic helpers without Vue or Inertia dependencies.
- `types` owns contracts shared across domains. Component-specific contracts stay in that component directory.
- `assets` owns authored frontend assets. Vendor-source provenance is recorded in the Task 2 manifest.
- `app.ts` and `ssr.ts` remain the client and server-rendering entrypoints.

## Component directory standard

Every UI primitive and reusable application component receives a PascalCase directory matching the component name.
The standard files are:

| File | Responsibility |
| --- | --- |
| `<Name>.vue` | Template, minimal component orchestration, and component-scoped styles only when utilities/tokens are insufficient |
| `index.ts` | Supported public exports for the directory |
| `types.ts` | Props, emits, slot contracts, and related component-only types |
| `variants.ts` | Class Variance Authority definitions and variant-derived types |
| `use<Name>.ts` | Stateful component behavior only when extracting it makes the SFC clearer and testable |
| `utils.ts` | Pure component-specific transformations |
| `constants.ts` | Stable component-specific values with no runtime state |

Only create files that carry a responsibility. Empty standard files and one-line indirection are prohibited. Use
ordinary `.ts` modules for authored component contracts; `.d.ts` is reserved for ambient declarations.

### SFC responsibility and size policy

Responsibility is the primary test, but the following script budgets act as review triggers:

- UI primitive `<script setup>`: 40 lines.
- Reusable application component `<script setup>`: 60 lines.
- Inertia page `<script setup>`: 40 lines.

Generated type declarations, import blocks, and blank lines are excluded from the budget. Exceeding a budget is not an
automatic failure, but requires a narrow, documented ESLint exception explaining why extraction would make ownership
less clear. Exceptions are exact-file entries, never directory-wide suppressions.

Extract when code owns a separable concern: variants, public types, deterministic transforms, reusable state,
third-party adapter behavior, or workflow coordination. Do not extract arbitrary chunks to satisfy a counter. Small
files are a consequence of clear ownership, not the product goal by themselves.

## Enforced dependency direction

```text
pages -> layouts -> application components -> UI primitives -> pure helpers
  |          |                 |                     |
  +----------+-----------------+---------------------+--> composables/types as allowed
```

More specifically:

- Pages may import layouts, application components, UI primitives, composables, shared types, helpers, and generated
  routes/actions.
- Layouts may import application components, UI primitives, composables, shared types, helpers, and generated routes;
  they may not import pages.
- Application components may import UI primitives, other application components, composables, shared types, helpers,
  and generated routes; they may not import pages or layouts.
- UI primitives may import other UI primitives, component-local modules, composables with no product dependency,
  shared generic types, and helpers; they may not import application components, routes, Inertia, pages, or layouts.
- Composables may depend on other composables, types, VueUse, Vue, Inertia when the composable explicitly owns an
  Inertia concern, and pure helpers; they may not import rendering layers.
- Pure helpers may not import Vue, VueUse, Inertia, or rendering layers.

ESLint and architecture tests must enforce these directions, PascalCase component directories, public `index.ts`
imports, forbidden deep imports, naming conventions, and the exact-file script-budget exception list.

## Design-token strategy

Argon Sass variables must be translated into semantic CSS custom properties and Tailwind CSS 4 theme tokens. Do not
copy the Sass build into the application or scatter source hex values through Vue templates.

The token inventory must cover:

- Brand, neutral, semantic, and data-visualization colors.
- Canvas, elevated, translucent, inset, interactive, and overlay surfaces.
- Text, icon, border, divider, focus, disabled, and selection states.
- Font families, size scale, weight, line height, letter spacing, and text transforms.
- Spacing, content widths, grid gutters, breakpoints, radii, border widths, shadows, and blur.
- Transition duration, easing, transforms, entrance/exit motion, and reduced-motion fallbacks.
- Layering for navigation, sticky content, dropdowns, popovers, modals, toasts, and devtools.

Tokens are defined once, mapped to Tailwind utilities where appropriate, and consumed semantically. Light and dark
themes receive explicit values. Temporary compatibility mappings must be named, documented, and removed in Task 18.

## Primitive implementation standard

Each primitive must be built from a source audit rather than its most obvious screenshot. For every component:

1. Inventory the Argon markup, modifiers, Sass variables, JavaScript behavior, examples, and responsive states.
2. Inventory the application's current API and usage sites.
3. Define a typed Vue API for intent rather than Bootstrap class names.
4. Select the accessible behavior foundation: native HTML first, then Reka UI or an existing dependency where useful.
5. Implement semantic tokens and CVA variants.
6. Forward meaningful HTML attributes and expose typed slots/events.
7. Cover keyboard, focus, hover, active, disabled, loading, invalid, empty, and overflow states.
8. Compare light/dark and mobile/tablet/desktop rendering with the licensed target.
9. Add focused tests for meaningful behavior and accessibility contracts.
10. Record deliberate visual or behavioral divergence in the Development task.

Variant axes should remain consistent across the kit:

- `variant` expresses semantic or visual intent such as primary, secondary, success, warning, danger, info, neutral,
  outline, ghost, or link.
- `size` expresses the control scale.
- `shape` expresses geometry only when the component genuinely supports it.
- Booleans describe independent state such as disabled, loading, block, dismissible, or selected.
- Surface treatment should not be overloaded into semantic color names.

## Floating form system

The form system is a first-class workstream because it must reconcile Argon appearance with more behavior than the
reference kit demonstrates. The supported family includes at least text, email, password, number, currency, phone,
URL, search, textarea, select, async select, checkbox, radio, switch, date/time controls, and file input.

All applicable controls share a typed field contract:

- `modelValue` and typed `update:modelValue` behavior.
- Label, description, hint, error, required, optional, disabled, readonly, loading, and autocomplete states.
- Stable generated IDs with explicit ID overrides.
- Correct `aria-describedby`, `aria-invalid`, label association, and error announcement.
- Prefix, suffix, leading icon, trailing icon, clear, reveal, and action slots where meaningful.
- Current formatting and masking behavior, including currency and phone input.
- Native HTML attributes and form submission behavior.
- Controlled focus, blur, input, change, clear, and search events.
- Inertia validation compatibility without an Inertia dependency inside the UI primitive.

The floating label must remain positioned correctly for empty, focused, filled, autofilled, invalid, disabled,
readonly, and browser-restored values. Password-manager and browser-autofill behavior must be checked in Chromium and
WebKit. Textareas and non-text controls need intentional floating-label behavior; CSS tricks that only work for
`:placeholder-shown` are insufficient.

## Advanced behavior adapters

The original kit uses Bootstrap-oriented plugins. Each capability will be classified before implementation:

| Capability | Preferred adaptation path |
| --- | --- |
| Dropdowns, popovers, tooltips, accordions, tabs, dialogs | Existing Reka UI behavior with Argon presentation |
| Charts | Existing Unovis integration with Argon tokens and chart defaults |
| Animation | CSS transitions or Motion for Vue; respect reduced motion |
| Date and time | Existing/native behavior first; approve a Vue-native dependency only if requirements exceed it |
| Select/search | Existing application controls and accessible Vue behavior |
| Data tables | First-party table composition with server-state compatibility |
| Kanban and drag/drop | Evaluate native/Vue-native options; add a dependency only after an interaction and accessibility spike |
| Calendar | Evaluate requirements and existing dependencies before selecting an adapter |
| Rich text | Treat content sanitation, keyboard behavior, and bundle cost as acceptance requirements |
| Maps | Implement only where the example or product requires them; lazy-load the selected adapter |

No original jQuery or Bootstrap plugin is copied blindly. Adapter components isolate any approved third-party library,
normalize it to typed Vue props/events, lazy-load expensive code where possible, and keep vendor-specific types out of
pages.

Task 8 established the following concrete boundaries:

| Capability | Application boundary | Loading decision |
| --- | --- | --- |
| Dropdown, tooltip, popover, accordion, tabs, modal, toast | Existing Nuxt UI and Reka UI primitives | Shared foundation |
| Table and pagination | Existing `IndexTableFrame` and `IndexPagination` compositions | Shared foundation |
| Chart | `AdvancedChart` over Unovis, with a textual data table fallback | Lazy component and dynamic vendor imports |
| Date and time | `AdvancedDateTime` over native date and time inputs | Eager, no additional picker runtime |
| Select and upload | Existing form components over Nuxt UI | Shared foundation |
| Kanban | `AdvancedKanban` with pointer and Left/Right Arrow movement | Eager first-party interaction |
| Calendar | `AdvancedCalendar` with an ISO date string public boundary | Eager; vendor date values remain internal |
| Rich text | `AdvancedEditor` over Nuxt UI and TipTap with a string HTML boundary | Lazy component |
| Map | `AdvancedMap` over MapLibre GL with a textual marker list | Lazy component and dynamic JS/CSS imports |

The adapter capability registry in `resources/js/lib/advancedBehaviorCapabilities.ts` is the source for gallery and
architecture checks. Expensive adapters are exported as `LazyAdvancedChart`, `LazyAdvancedEditor`, and
`LazyAdvancedMap`; ordinary application entry points do not import their runtimes.

Every direct dependency added for this task was evaluated before adoption:

| Package | Need and compatibility | License and maintenance | Accessibility and bundle impact |
| --- | --- | --- | --- |
| `vite-plus` 0.3.0 | Runs the existing Vue, Inertia, Laravel, Wayfinder, and Nuxt UI Vite plugins through the current Laravel starter-kit toolchain | MIT; active VoidZero project, pinned for reproducibility | Development-only; no rendered interface or shipped CLI |
| `@unovis/ts` 1.6.7 | Required peer for the existing `@unovis/vue` integration | Apache-2.0; maintained with Unovis Vue | Chart provides a label and textual data; runtime stays behind the lazy chart boundary |
| `@internationalized/date` 3.12.3 | Converts ISO strings at the calendar boundary and is already used by Nuxt UI | Apache-2.0; maintained by Adobe React Spectrum | Nuxt UI owns focus and keyboard behavior; shared date-model code is small |
| `maplibre-gl` 6.6.0 | Maintained, non-proprietary replacement for manifest map behavior | BSD-3-Clause; active MapLibre community | Adapter adds keyboard markers, status, errors, and a textual list; runtime and CSS load dynamically |
| `vue-eslint-parser` 10.x | Explicitly satisfies the upgraded Vue ESLint peer contract | MIT; maintained with the Vue ESLint toolchain | Development-only; no runtime bundle impact |

Vite+ runs its fast, non-type-aware lint pass as part of `vp check`. Vue SFC and path-alias type analysis remains with
`vue-tsc`, while the repository's ESLint architecture rules and Prettier checks remain explicit quality gates. This
avoids treating Vite+'s TypeScript-only checker as authoritative for Vue files it cannot resolve.

## Protected reference gallery

All licensed Argon examples must be discoverable inside the application without becoming public product routes. The
gallery will use a dedicated authenticated layout and an explicit permission. It will include:

- A searchable manifest index grouped by the eight source families.
- A route for every one of the 69 source examples.
- Component and state laboratories for primitives, including interactive controls for supported variants.
- Light/dark switching and mobile/tablet/desktop viewport checks.
- Notes for source path, implementation status, dependencies, divergences, and consuming application pages.
- Lazy loading so the gallery does not inflate ordinary application bundles.

Gallery completeness is tested against the Task 2 manifest. Production availability is a deployment decision, but
authorization remains mandatory in every environment.

## Current application migration method

Every current route family follows the same vertical-slice sequence:

1. Record the current named routes, permissions, policies, Inertia props, deferred props, forms, filters, mutations,
   loading states, empty states, error states, responsive behavior, and browser coverage.
2. Map each page to the closest Argon layout and component composition. Record intentional departures driven by
   product behavior.
3. Migrate or create the required UI primitives first.
4. Create application-level defaults and workflow components only where they own real product behavior.
5. Rebuild the page with page-family components colocated under `pages/<Family>/Components`.
6. Retain the original backend contracts. Any backend change requires its own explicit justification and tests.
7. Run focused PHP, Vue, type, lint, build, accessibility, responsive, visual, and browser verification.
8. Remove the superseded section/components for that migrated slice.
9. Search for legacy imports and styling tokens before marking the slice complete.

Representative baseline views must be captured before migration for public/authentication, workspace/dashboard, CRM,
settings, administration, and Development workflows. Each family must be checked at a minimum of mobile, tablet, and
desktop widths in light and dark themes.

## Delivery sequence

Each numbered item is represented by one live Development task and one pull request. A task is not complete merely
because its code is written: it must meet its acceptance criteria, pass the required verification, be reviewed, and
complete the user-testing workflow where applicable.

### 1. Baseline, blueprint, and live tracking

- Record authorization, sources, scope, precedence, target architecture, component standards, and migration rules.
- Capture the exact current application and Argon example counts.
- Create the full ordered Development task cohort with acceptance criteria, assignment, and priority.
- Establish and record baseline formatting, lint, typecheck, build, PHP, and browser results.
- Open the Task 1 pull request, attach it to the Development record, and request testing.

### 2. Licensed source and asset manifest

- Inventory all 69 HTML examples with stable IDs, family, source path, title, layout, assets, plugins, and behaviors.
- Inventory fonts, icons, images, textures, logos, JavaScript, and Sass with provenance and distribution classification.
- Detect duplicate assets and map each source item to an authored, adapted, generated, runtime-only, or excluded state.
- Add automated completeness and missing-file checks.
- Document unresolved licensing questions for the future starter-kit review without blocking this application work.

### 3. Frontend organization and transitional guardrails

- Introduce `components/Ui` and the component-directory public API standard.
- Update the current frontend architecture document and ESLint dependency boundaries to the approved target.
- Add naming, public-export, deep-import, and SFC script-budget enforcement with exact exceptions.
- Define the page-family colocation convention and progressive `sections` retirement rule.
- Prove guardrails with focused architecture tests while preserving current builds.

### 4. Scoped Argon foundations

- Translate Argon colors, typography, spacing, grids, radii, borders, shadows, blur, motion, and layer values into
  semantic light/dark tokens.
- Add scoped reset/base styles and body/layout behavior without leaking into Laravel debug or third-party surfaces.
- Establish font and icon loading with provenance and no avoidable layout shift.
- Add token documentation and contract checks for raw-value drift.
- Compare foundation specimens against the local kit at supported breakpoints.

### 5. Core visual primitives

- Implement the reusable visual component set: buttons, button groups, cards, badges, avatars, alerts, dividers,
  typography, icons, lists, breadcrumbs, progress, skeletons, spinners, empty states, and stat surfaces.
- Reproduce supported Argon variants and states with typed APIs and CVA definitions.
- Add focused accessibility and behavior coverage where a primitive owns interaction.
- Provide gallery specimens for every supported state in both themes.

### 6. Floating form system

- Implement the shared field shell and all applicable floating-label controls.
- Preserve current options, formatting, masking, slots, validation, and Inertia compatibility.
- Verify empty, focused, filled, autofilled, invalid, disabled, readonly, restored, and loading states.
- Cover keyboard use, screen-reader associations, Chromium/WebKit autofill, mobile input modes, and light/dark rendering.
- Add a migration reference from every current form component to its target primitive/application wrapper.

### 7. Layouts and navigation

- Implement public, authentication, application, settings, and gallery layout shells.
- Reproduce Argon sidebar, navbar, breadcrumbs, footer, configurator/theme controls, mobile navigation, and responsive
  collapse behavior.
- Preserve active-route, permission-filtering, logout, SSR hydration, scroll, focus, and Wayfinder behavior.
- Add browser coverage for keyboard navigation, mobile overlays, theme persistence, and unauthorized links.

### 8. Advanced adapters

- Implement or adapt dropdown, tooltip, popover, accordion, tabs, modal, toast, table, pagination, charts, date/time,
  select, upload, kanban, calendar, rich-text, and map capabilities required by the manifest.
- Record the evaluation for every new dependency before adding it.
- Isolate third-party behavior behind typed Vue adapters and lazy-load expensive gallery/application features.
- Verify cleanup, focus management, keyboard behavior, reduced motion, SSR safety, and bundle impact.

### 9. Protected gallery shell and component laboratory

- Add permission-protected gallery routes, navigation, index, search, filters, and manifest status.
- Add interactive laboratories for foundation tokens, primitives, forms, layouts, and advanced adapters.
- Provide theme and viewport controls plus source/divergence notes.
- Test authorization, manifest completeness, lazy chunks, and ordinary application bundle isolation.

### 10. General Argon reference pages

- Port all 22 examples in the general `pages` family to Vue/Inertia gallery resources.
- Use shared layouts/primitives rather than page-local copies of kit markup.
- Reproduce responsive and interactive behavior, recording deliberate divergences.
- Verify every manifest ID resolves to an authorized gallery route and renders in both themes.

### 11. Identity and project Argon reference pages

- Port 17 authentication, 4 account, 3 project, and 5 team examples.
- Share compositional pieces without coupling gallery examples to application auth/domain mutations.
- Cover alternative layouts, validation presentations, profile/account states, project views, and team views.
- Verify all 29 manifest IDs, themes, breakpoints, keyboard paths, and asset references.

### 12. Dashboard, application, and ecommerce Argon reference pages

- Port 5 dashboard, 5 application, and 8 ecommerce examples.
- Exercise charts, tables, calendars, kanban, maps, uploads, editors, and other advanced adapters through the gallery.
- Preserve source data shape for visual fidelity while keeping fixtures inert and non-production.
- Verify all 18 manifest IDs, lazy chunks, themes, breakpoints, and interactions.

### 13. Public, privacy, error, and authentication migration

- Rebuild public landing/intake, privacy, branded error, login, password, invitation/registration, verification,
  confirmation, and two-factor views.
- Preserve Fortify flows, redirects, rate limits, validation, autofill, password managers, recovery codes, and privacy
  fallback behavior.
- Remove superseded public/auth sections only after focused feature and browser coverage passes.
- Compare anonymous mobile/desktop light/dark states against the approved Argon direction.

### 14. Workspace and dashboard migration

- Rebuild the authenticated workspace shell and dashboard pages with Argon navigation, cards, metrics, activity,
  empty/loading/error states, and responsive behavior.
- Preserve role-aware navigation, deferred data, polling/prefetching where present, and SSR behavior.
- Move one-family components beside their pages and promote only proven reusable contracts.
- Remove replaced sections and verify routes, permissions, queries, browser workflows, and both themes.

### 15. CRM migration

- Rebuild CRM indexes, detail layouts, forms, selectors, filters, pagination, notes, and domain workflows.
- Preserve typed filtering, query strings, async selection, permission boundaries, mutations, feedback, and data
  ownership behavior.
- Keep route/business coordination outside UI primitives.
- Remove superseded CRM sections and verify feature, query-count, type, build, browser, responsive, and theme behavior.

### 16. Settings and administration migration

- Rebuild profile, password/security, appearance, team/user/role/permission, and other administration surfaces.
- Preserve authorization, confirmation, destructive-action safeguards, two-factor behavior, and application defaults.
- Reuse Argon primitives through application contracts instead of page-specific styling copies.
- Remove replaced sections and verify Super Admin and restricted-user browser paths.

### 17. Development workflow migration

- Rebuild requests, review, roadmap, task detail, progress, notes, links, testing rounds, and result-recording surfaces.
- Preserve the Action boundary, explicit actor attribution, transition guards, assignments, ordering, and audit history.
- Verify role-specific navigation and every supported lifecycle transition in feature and browser tests.
- Remove superseded Development sections and confirm this blueprint still points to live tracking as the sole truth.

### 18. Legacy removal and final hardening

- Confirm all 35 baseline pages, 88 baseline routes, and 69 Argon examples are mapped and verified.
- Remove `resources/js/sections`, obsolete components, Nuxt UI-specific application remnants, compatibility tokens,
  unused assets, transitional adapters, dead styles, and stale dependencies.
- Run format, lint, typecheck, client/SSR build, full PHP suite, full browser suite, accessibility review, responsive and
  visual matrices, bundle analysis, dependency/license audit, and dead-import searches.
- Update maintained architecture and contributor documentation to describe only the final implementation.
- Produce a final divergence register and a clean basis for the later starter-kit extraction decision.

## Per-task operating procedure

Every task follows this sequence:

1. Confirm the live Development task, acceptance criteria, source references, route/page inventory, and prerequisites.
2. Create a `feature/` branch dedicated to that task.
3. Capture or confirm the relevant before-state and automated baseline.
4. Implement the smallest complete vertical slice; do not mix unrelated cleanup into the branch.
5. Update focused tests and guardrails in the same task.
6. Run format, lint, typecheck, client/SSR build, focused PHP tests, and relevant browser checks.
7. Perform light/dark and mobile/tablet/desktop comparison for affected surfaces.
8. Search for superseded imports, assets, tokens, and files.
9. Review the diff for behavior, accessibility, architecture, security, bundle, and licensing risks.
10. Commit intentionally, push, and open a draft pull request with actual verification results.
11. Attach the pull request to the Development task, add implementation notes, and request user testing.
12. Record each test round in the Development workspace; complete the task only through the approved lifecycle.

## Verification matrix

The exact commands may evolve with the repository, but each pull request records the commands and results actually
run. The program-level minimum is:

| Concern | Evidence |
| --- | --- |
| PHP formatting | Pint on changed PHP files |
| PHP behavior | Focused Pest tests; full suite at integration milestones and Task 18 |
| Frontend formatting | Prettier check |
| Frontend conventions | ESLint and architecture contracts |
| Type safety | `vue-tsc --noEmit` |
| Bundling and SSR | Vite client and SSR production builds |
| Route behavior | Laravel feature tests and Wayfinder use |
| Interaction | Pest browser or Playwright-backed browser workflows |
| Accessibility | Semantic review, keyboard paths, focus, labels, announcements, contrast, and reduced motion |
| Responsive behavior | Mobile, tablet, and desktop widths |
| Themes | Light and dark on every affected representative surface |
| Fidelity | Side-by-side comparison with the local licensed kit/demo |
| Performance | Chunk/bundle review, lazy-loading checks, and no avoidable layout shift |
| Licensing | Manifest provenance and dependency/asset review |

## Program definition of done

The application overhaul is ready for the later starter-kit discussion only when:

- Every live task in this sequence has passed its required testing workflow.
- All 35 baseline application pages and 88 baseline routes retain their required behavior and authorization.
- All 69 licensed examples are represented in the protected gallery or carry an approved, documented exclusion.
- The primitive and floating-form APIs are typed, documented through examples, accessible, responsive, and tested.
- Light and dark themes are coherent across all application and gallery surfaces.
- `resources/js/sections` and all superseded frontend code, assets, dependencies, and compatibility layers are gone.
- Formatting, lint, typecheck, client/SSR build, full PHP suite, and full browser suite pass.
- Remaining visual or functional divergences are explicit decisions rather than overlooked gaps.
- Maintained documentation describes the final code accurately and the Development database contains the durable audit
  trail of implementation, testing, and approval.
