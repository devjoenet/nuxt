# Southeast Code Roadmap

This roadmap tracks the work needed to turn the current Laravel/Inertia starter kit into a functional Southeast Code
operating app: customer-facing marketing, a basic CRM, and an admin area for users, roles, permissions, queues, jobs,
audit trails, and business operations.

If at any time while adding a task, a decision with an unclear answer exists, it must be added here. 

## Implementation Contract

These defaults make the roadmap executable even when `docs/00-decision-points.md` has not been completed yet. If a
decision is later made differently, update this roadmap and the affected phase document before implementation continues.

- Build the Southeast Code internal operating app first. Starter-kit reuse is secondary and must not keep generic demo
  screens, generic copy, or placeholder APIs alive.
- Ship v1 as a single Southeast Code workspace. Multi-company tenancy and a client portal are later work.
- Use the CRM vocabulary `Lead -> Client` for v1. A `Lead` carries pipeline stages that can represent opportunity
  progress; do not add a separate `Opportunity` model unless the decision document is completed differently.
- Model `Organization` as the account/client record. `Contact` belongs to an organization when one is known, but an
  intake submission may create an individual contact without an organization.
- Keep pipeline/status values code-owned with PHP enums for v1. Admin-editable pipeline configuration is later unless
  explicitly pulled into scope.
- Use server-side Laravel authorization as the source of truth. Inertia may receive permission flags for navigation and
  button visibility, but hidden UI is never the authorization boundary.
- Use named routes and Wayfinder-generated imports for frontend links and forms. Do not hardcode application URLs in Vue
  pages.
- Use Pest feature tests for every shipped workflow and policy/authorization boundary. Add focused unit tests only where
  domain logic is isolated enough to make them useful.
- Use database queues for local/simple development defaults and Redis plus Horizon for staging/production operations.
  Tests should fake queues/notifications unless a persistence or Horizon authorization behavior is under test.
- Use the business notification email for internal lead/reminder mail, keep visitor confirmations and scheduling links
  deferred, and retain audit records for 365 days with scheduled cleanup.
- Deploy staging and production as separate Laravel Forge-managed sites with PHP 8.5, MySQL/Redis, Supervisor-managed
  Horizon workers, Forge scheduler jobs, daily encrypted backups with isolated restore tests, Forge logs, `/up`
  monitoring, and named Operations Lead/Technical Maintainer ownership.
- 
## Active Development Tasks

The next tasks are ordered around the remaining unchecked work and the decisions that can change implementation scope. 

__Note: Tasks that have been completed are moved to `./completed-roadmap.md`__

### Task 12 — Resolve Larascan Findings And Harden Security Gates

The first Larascan run reported 54 passed, 22 failed, and 12 skipped checks, with a reported highest severity of
`CRITICAL`. Treat this as a triage task rather than a blind fix list: several failures are false positives caused by
Larascan not recognizing Laravel 13 native attributes, Fortify's configured limiters, or controller middleware
attributes. Do not add a blanket baseline or lower the CI fail threshold until each failure has an owner and disposition.

- [ ] **Fix the scan integration first.** Change `.github/workflows/larascan.yml` from PHP 8.4 to PHP 8.5, copy and key
  `.env.example` before booting Artisan, prepare the SQLite database if the scan requires application boot, and add a
  clean CI run that proves the scan itself is executable. Keep the existing `fail_on=high` gate.
- [ ] **Remove or gate newly exposed diagnostic tooling.** Review why `garygreen/pretty-routes` and
  `jaydeep/laravel-time-machine` were added to production `require` dependencies. Remove them if they were incidental;
  otherwise move them to development-only scope, disable their dashboards outside local development, protect any local
  routes, ensure `storage/time-machine/` is ignored and empty from source control, and add production assertions that
  `/routes` and `/time-machine` are unavailable.
- [ ] **Close the authentication findings.** Add focused tests for repeated failed login and 2FA challenge requests,
  confirm the custom `LoginRequest` limiter and Fortify `two-factor` limiter protect the POST flows, and decide whether
  the password-confirmed 2FA management mutations need an additional explicit throttle. Do not throttle harmless GET
  screens solely to satisfy a static rule. Add a narrow documented Larascan suppression for the GET/static false
  positives only after the runtime behavior is covered.
- [ ] **Resolve the model and generated-secret findings safely.** Audit the three `forceFill()` call sites individually:
  use explicit attribute assignment where it improves mass-assignment clarity, retain a bypass only for intentional
  internal state changes, and cover password reset, admin 2FA reset, and reminder persistence with regression tests.
  Replace the admin invitation's generated password seed with `Str::password()`; keep remember tokens as tokens rather
  than treating them as passwords. Record narrow suppressions for intentional internal writes instead of suppressing
  the rule globally.
- [ ] **Record existing Laravel controls as verified false positives.** `User` already hides password data and uses the
  `hashed` cast; the parameterized email-verification endpoint already has `signed` and `throttle` middleware; and the
  custom `SecurityHeaders` middleware already emits `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy`,
  and production HSTS. Add serialization, invalid-signature, and runtime-header assertions, then use the scanner's
  supported narrow ignore/baseline mechanism with file/rule justification so these findings do not obscure real
  regressions.
- [ ] **Harden environment and session profiles.** Make `.env.example` scanner-visible and secret-free for the full
  supported key set, including queue, Redis, mail, activity-log, Horizon, Larascan probe, and AWS endpoint keys. Define
  separate local/testing versus staging/production expectations: production must use `APP_ENV=production`,
  `APP_DEBUG=false`, HTTPS-only session cookies, encrypted sessions, an explicit secure same-site policy, and a
  non-debug log level. Confirm whether the debug-blacklist check is supported by this Laravel version before adding
  unsupported configuration.
- [ ] **Harden deployment/runtime settings.** Set S3 disk visibility explicitly to `private`; validate that the
  application does not rely on remote URL wrappers before disabling `allow_url_fopen`; disable `expose_php` in the PHP
  8.5 deployment runtime; add custom 500 and 503 error pages; and configure the Larascan HTTPS runtime probe against
  staging so HSTS, headers, cookies, HTTPS redirects, and server disclosure are checked on a real response.
- [ ] **Close repository and dependency hygiene gaps.** Add Dependabot coverage for Composer, npm, and GitHub Actions;
  add `public/.well-known/security.txt` with the approved security contact and expiry; and upgrade the current esbuild
  `0.27.7` to `0.28.1` or newer before relying on the frontend development tooling on Windows. The current advisory
  affects esbuild versions `>=0.27.3` and `<0.28.1`.
- [ ] **Define the completion gate.** A clean run has no unresolved high/critical findings, every accepted low/info
  finding has a documented environment or false-positive rationale, the runtime probe passes in staging, diagnostic
  routes are unavailable outside local development, and the Larascan report is uploaded without hiding new findings.

### Task 13 — Make Landing QA And Feedback A First-Class Workflow

- [x] Add a permission-gated `/admin/user-testing` checklist seeded with every route-backed page plus shared error states;
  preserve the welcome-page review brief as the initial note.
- [x] Persist approval state, reviewer, timestamp, and notes in `user_testing_pages`; expose an authenticated updates JSON
  endpoint that accepts `since` for polling and change retrieval.
- [x] Replace the public service-interest scalar with a chip-based multi-select and retain a compatibility value while
  storing the complete selection set in `leads.service_interests`.
- [x] Replace the fixed budget bands with a numeric currency input and server-side numeric validation.
- [x] Remove public `mailto:` links in favor of the general contact form and correct floating-label positioning for filled
  controls.
- [x] Add `motion-v` entrance animations with user reduced-motion support; use one-way `viewport.once` reveals so sections
  remain settled after they enter, and remove the Reality card color-fill transition.
- [ ] Run the focused Pest feature tests, frontend typecheck, and browser smoke pass; visually review the landing page at
  mobile, desktop, light, dark, reduced-motion, and keyboard-only states.

### Task 14 — Resolve Landing Review Follow-ups

The welcome-page testing record contains the following unapproved findings. Keep these tasks separate from the completed
landing implementation so the original feedback remains traceable:

- [ ] Rework or remove the entrance animations for the Reality, Transformation, and Services sections while preserving
  the hero, contact form, and Process section treatment.
- [ ] Make section entrance animations settle after first entry and prevent replay when scrolling in either direction.
- [ ] Remove the footer's `Use the contact form` text link and keep the contact action as the available link icon.
- [ ] Fix the slight background-color anomaly around the contact form multi-select inputs; request the user's screen
  capture before finalizing the visual correction.
- [ ] Change the estimated budget field to a currency-formatting string input and correct its placeholder behavior;
  request the user's screen capture before finalizing the visual correction.

### Task 15 — Rework Public Privacy Presentation

The privacy-page testing record asks whether the policy should remain a scrollable page or become an on-demand surface.
Resolve that presentation choice before implementation; this is a UX follow-up, not a change to the ratified legal scope.

- [ ] Decide whether the privacy policy remains a dedicated page or becomes a reusable modal/dialog available from public
  pages; preserve a directly addressable policy URL either way.
- [ ] If a modal/dialog is selected, load the policy content lazily and keep keyboard, screen-reader, focus, and deep-link
  behavior accessible.
- [ ] If the page remains, reduce or remove unnecessary scrolling while preserving readable legal-document spacing and
  responsive behavior.
- [ ] Add focused coverage for the selected presentation and its public navigation entry points.

### Task 16 — Brand And Theme Shared Error Pages

User testing found that the shared 403, 404, 419, 429, 500, and 503 states are not yet fully compatible with dark mode
and do not carry Southeast Code branding.

- [ ] Apply the shared light/dark theme tokens and verify readable contrast across every supported error state.
- [ ] Add Southeast Code branding, useful recovery copy, and the appropriate navigation or home action without exposing
  debug details.
- [ ] Verify the error-page variants at responsive breakpoints and add focused coverage for status-specific rendering.

### Task 17 — Refine Audit Log Filters

User testing found three audit-screen issues: the search placeholder overlaps the magnifying-glass icon, the Subject
model-or-ID field is too verbose/truncated, and Event, Causer, and Subject should offer constrained multi-select options.

- [ ] Fix the Search activity field spacing so its placeholder and icon never overlap in supported themes and widths.
- [ ] Shorten the Subject model-or-ID label/placeholder while keeping the filter's meaning clear.
- [ ] Replace the Event, Causer, and Subject free-text filters with accessible multi-select inputs populated only from
  valid available options.
- [ ] Preserve server-side filtering, authorization, and empty-state behavior; add focused coverage for the new filter
  combinations.

### Task 18 — Repair User Invitation And Verification Flows

User testing found that the invitation form sends a password-reset email without explaining the actual onboarding step,
the resend-confirmation action generates a link that returns 404, and the 2FA reset action shown for a user pending
email verification has no effect.

- [ ] Create a dedicated invitation/onboarding email that accurately explains the recipient's next step instead of
  presenting the flow as a generic password reset.
- [ ] Trace the resend-confirmation route and signed URL generation, then make the link resolve successfully from the
  delivered email; add regression coverage for the generated URL and endpoint.
- [ ] Make the pending-verification 2FA action either perform the intended password-confirmed reset or become clearly
  unavailable with explanatory UI; do not leave a button that silently does nothing.
- [ ] Add focused authorization, mail, signed-link, and state-transition tests for invitation, email verification, and
  2FA reset behavior.

### Task 19 — Simplify Role Editing And Super Admin Presentation

User testing requested a role-first editor with one larger role selector and a dynamically loaded permission list, plus
an informational treatment for the non-editable Super Admin role.

- [ ] Replace the all-roles permission lists with a prominent accessible role selector and load the selected role's
  permissions into one focused list without weakening server-side authorization.
- [ ] Render the Super Admin permissions area as a compact info banner stating that all permissions are granted by the
  server-side Gate bypass and that the role is not editable from the UI, with an info icon on the left.
- [ ] Preserve guarded role/permission behavior, loading, empty, and error states; add focused coverage for role
  selection, permission updates, and Super Admin non-editability.

### Task 21 — Restore CRM Detail Browser Smoke Coverage

- [ ] Diagnose why Pest's temporary browser HTTP server blank-renders CRM lead, organization, and contact detail URLs
  while list pages and the other browser smoke flows render normally.
- [ ] Reinstate focused CRM browser coverage that separately verifies View link navigation and the linked detail-route
  response for leads, organizations, and contacts.
- [ ] Run the browser suite and full quality contract after the detail-route rendering issue is resolved.

## Later, Not MVP

- [ ] Client portal for project status, shared files, notes, and approvals.
- [ ] Proposal, statement-of-work, contract, and invoice workflows.
- [ ] Payment and accounting integrations.
- [ ] Advanced reporting dashboards and saved report views.
- [ ] Email campaign/newsletter tooling.
- [ ] Knowledge base or internal SOP section.
- [ ] Public case studies and portfolio management.

## Assumptions

- Southeast Code becoming operational is the priority; generic starter-kit polish is secondary.
- CRM v1 should stay practical: leads, contacts, organizations, follow-ups, and admin controls before proposals,
  billing, or client portals.
- Client portal, invoicing, contracts, and advanced reporting should wait until the core marketing-to-lead-to-client
  workflow is real.
- Every code change that implements this roadmap should ship with focused Pest coverage and user-run verification
  commands.
