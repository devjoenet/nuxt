# Southeast Code Roadmap

This roadmap tracks the work needed to turn the current Laravel/Inertia starter kit into a functional Southeast Code
operating app: customer-facing marketing, a basic CRM, and an admin area for users, roles, permissions, queues, jobs,
audit trails, and business operations.

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

## Current Stock

- [x] Laravel 13 application running on PHP 8.5.
- [x] Inertia, Vue 3, Nuxt UI, Tailwind CSS, TypeScript, and Wayfinder are installed.
- [x] Fortify authentication is present with login, registration, email verification, password reset, password
  confirmation, and two-factor authentication screens.
- [x] Profile and security settings exist for authenticated users.
- [x] A Southeast Code marketing landing page shell exists with brand copy, theme colors, landing assets, and
  operations-focused messaging.
- [x] Horizon and Horizon UI packages are installed.
- [x] Queue, failed-job, and job-batch tables exist.
- [x] Spatie Permission and Activitylog migrations/config are present.
- [x] Pest baseline tests exist for auth, dashboard access, settings, architecture, and Horizon authorization
  expectations.
- [x] CRM screens no longer use external Nuxt demo APIs; they use first-party Inertia data.
- [x] First-party CRM tables, models, controllers, policies, factories, and seeders exist for the shipped workspace.
- [x] `User` is wired to Spatie roles/permissions.
- [x] Horizon access is protected by business roles and permissions; the unused third-party Horizon UI remains undiscovered.
- [x] Queue intent is explicit: local `.env.example` defaults to database queues while staging/production Horizon uses Redis.
- [x] Notification policy and audit retention are explicit: internal mail uses the business setting, deployment alerts use
  HORIZON_NOTIFICATION_EMAIL, visitor confirmations/scheduling links are deferred, and audit cleanup retains 365 days.
- [x] README and CI match the current Laravel 13, PHP 8.5, and Southeast Code application direction.
- [x] Shared expressive form controls cover the shipped auth, settings, marketing, CRM, admin, audit, account, and
  2FA forms with outlined/filled variants, floating labels, validation states, light/dark themes, and custom selects.

## Open Decisions To Ratify

`docs/00-decision-points.md` tracks the unresolved choices that can still change implementation details. The roadmap
defaults above remain the executable path until those decisions are made.

- [x] DP-01: choose the v1 success metric and launch gate. Task 8 ratifies operational CRM readiness: inbound
  opportunities become first-party records that authorized staff can locate, assign, manage, and advance.
- [x] DP-02: confirm the final registration/account lifecycle after invite-based admin management exists. Staff access
  is invite-first and public registration routes are removed.
- [x] DP-03: decide whether clients ever log in during v1 or whether client access is explicitly deferred. Client login
  and portal access remain deferred; `Client Viewer` is reserved without an active client surface.
- [x] DP-04: confirm CRM model exceptions: separate opportunity records, individual clients, and conversion output.
  V1 uses the Lead pipeline, permits individual contacts, requires an organization for conversion, and creates no
  project.
- [x] DP-05: confirm whether pipeline/status values stay code-owned for v1 or become admin-configurable. V1 uses PHP
  enums and read-only pipeline values.
- [x] DP-06: define CRM ownership visibility for sales/ops users. CRM records use one nullable `owner_id`; Sales/Ops
  users see owned or unassigned records, Admin/Super Admin bypass ownership filters, only admins transfer ownership,
  and out-of-scope direct URLs return `404`.
- [x] DP-07: lock the public intake field set, consent copy, and spam controls. The implemented form, validation,
  consent checkbox, IP capture, and rate limit match the documented default.
- [x] DP-08: choose notification recipients, channels, and scheduling-link behavior. Internal lead alerts and follow-up
  reminders use the business notification email on the mail queue; visitor confirmations and scheduling links are
  deferred; Horizon failed-job and long-wait alerts use HORIZON_NOTIFICATION_EMAIL.
- [x] DP-09: define audit retention, redaction, and searchable fields. Audit records are retained for 365 days; note
  bodies, contact email/phone values, notification recipients, secrets/auth material, and exception messages are
  redacted; search covers description, log/event, causer, subject, and date range.
- [x] DP-10: confirm deployment target and production queue/Horizon operations. Laravel Forge is the ratified deployment
  control plane for separate staging/production sites with PHP 8.5, MySQL/Redis, Supervisor-managed Horizon, Forge
  scheduler jobs, daily encrypted backups, isolated restore tests, Forge logs, `/up` monitoring, and Operations
  Lead/Technical Maintainer ownership.
- [x] DP-11: standardize the UI component approach and responsive table behavior. Nuxt UI is the base with local
  wrappers for repeated form and workspace patterns.
- [x] DP-12: decide privacy/legal page requirements for public lead capture. A privacy page and contact-data consent are
  shipped; terms remain unnecessary unless the decision changes.

## Roadmap Checklist

### Foundation Cleanup

- [x] Update README/package messaging to Laravel 13, PHP 8.5, Inertia 3, and Southeast Code operating-app direction.
- [x] Align CI PHP version with Composer's PHP 8.5 requirement.
- [x] Remove or replace demo Nuxt dashboard references, placeholder GitHub links, fake teams, and external demo API
  calls. Production-facing demo APIs and links are gone, and `TeamsMenu.vue` now identifies the single Southeast Code
  workspace without fake team actions.
- [x] Define navigation groups for public marketing, authenticated workspace, admin/ops, settings, jobs, and audit.
- [x] Establish route/page naming conventions for marketing, CRM, admin, settings, jobs, and audit sections.
- [x] Rename `/customers` and the `Customers.vue` page to the CRM route/page namespace before building real data behind
  it. The shipped route is `/crm/leads` under the `Crm/*` page namespace.

### Marketing Site Bones

- [x] Finalize the one-page customer-facing flow: hero, services, process, proof/metrics, trust signals, and contact
  CTA.
- [x] Add a first-party contact/intake form that persists submissions as CRM leads.
- [x] Add thank-you state, internal notification, and basic spam/rate-limit protection.
- [x] Add SEO metadata, social image, sitemap/robots review, and accessible page structure.
- [x] Keep marketing content code-owned for v1; move admin-editable marketing content to later work unless the roadmap
  changes.
- [x] Add a focused test proving a visitor can submit the contact form and create a lead.

### CRM MVP

- [x] Add first-party CRM data model: organizations/accounts, contacts, leads, notes, activities, and tasks. Keep tags,
  separate opportunity records, and database-managed stages out of v1 unless a decision explicitly pulls them in.
- [x] Add migrations, models, factories, seeders, policies, requests, resources/data objects, and controllers for the
  CRM core.
- [x] Replace `Customers.vue` demo API data with real Inertia props/actions and server-side pagination/filtering.
- [x] Build list, detail, create, edit, archive, and delete flows for leads, contacts, and organizations. Task 8 adds
  first-party contact and organization workflows plus ownership-scoped visibility.
- [x] Add a lead status pipeline and conversion flow from lead to client. Create project records only if DP-04 is
  resolved in favor of project creation during conversion.
- [x] Add follow-up tasks, due dates, ownership, and activity history.
- [x] Add realistic local seed data for leads, clients, contacts, activities, and follow-ups.
- [x] Add focused Pest feature tests for CRM CRUD, validation, authorization, ownership scoping, filtering,
  pagination, and conversion workflows.

### Admin Section

- [x] Add an admin dashboard with business health cards: leads, qualified pipeline leads, follow-ups due, failed jobs,
  recent activity, and recent users.
- [x] Wire Spatie roles/permissions into `User`.
- [x] Seed default roles and permissions.
- [x] Enforce role/permission middleware and policies across admin, CRM, jobs, settings, and audit sections.
- [x] Build user management: invite, deactivate, verify status, assign roles, and reset 2FA/recovery where appropriate.
- [x] Build role/permission management with guarded access for super admins.
- [x] Add an activity log viewer for sensitive model changes and admin actions.
- [x] Add business settings for notification recipients, company profile, and default task rules. Keep lead statuses and
  pipeline stages code-owned/read-only unless DP-05 changes the default.

### Jobs, Queues, And Operations

- [x] Configure Redis plus Horizon for staging/production and keep local/simple defaults aligned with database queues.
- [x] Protect Horizon and Horizon UI using roles or permissions instead of the current empty email allow-list.
- [x] Add queue/job admin links for failed jobs, pending jobs, batches, job search, and Horizon views.
- [x] Add queued jobs for lead notifications and follow-up reminders. Add intake confirmations or activity digests only
  if DP-08 enables them.
- [x] Schedule Horizon snapshots.
- [x] Define queue wait and failed-job notification behavior.
- [x] Add job tags for CRM, notifications, imports, exports, and operational reminders.
- [x] Add operational runbook notes only after queue and Horizon behavior exists.

### Permissions And Audit

- [x] Define permission names around capabilities, not pages: `crm.view`, `crm.manage`, `admin.users.manage`,
  `admin.roles.manage`, `jobs.view`, `jobs.manage`, `settings.manage`, `audit.view`.
- [x] Hide navigation items based on permissions.
- [x] Add policies for CRM entities and admin-only operations. Ownership scoping remains a separate unfinished concern
  under DP-06.
- [x] Log create/update/delete events for CRM records, role changes, user changes, queue actions, and sensitive settings
  changes.
- [x] Apply the default audit redaction rules from Phase 4 and update them if DP-09 changes retention/redaction policy.
- [x] Add tests proving unauthorized users cannot see or perform admin, CRM, jobs, settings, or audit actions. Audit
  filtering, settings access, Horizon mutation logging, queue failure logging, and safe redaction are covered.

### Starter-Kit Quality

- [x] Replace empty/demo states with useful Southeast Code product empty states.
- [x] Add tests for marketing contact submission, CRM CRUD, permissions, Horizon/admin access, and queue dispatching.
- [x] Add browser smoke coverage for critical auth, marketing, CRM lists, admin, jobs, settings, audit, and expressive
  form control behavior.
- [x] Keep app-facing copy consistent with Southeast Code positioning from `docs/CompanyBrief.md`.
- [x] Keep app-facing colors aligned with `docs/ColorPalette.md`, while meeting contrast requirements in forms, tables,
  navigation, and status badges.
- [x] Keep the UI practical and operational: dense, scannable, restrained, and built for repeated daily use.
- [x] After implementation, run the relevant verification commands:
  - `composer test`
  - `composer test:types`
  - `composer test:lint`
  - `npm run build`
  - targeted checks such as `php artisan test --compact --filter=ContactFormTest`

## Development Task Lineup

The next tasks are ordered around the remaining unchecked work and the decisions that can change implementation scope.

### Task 8 — Complete CRM Entities And Ownership

- [x] Ratify DP-01's v1 success metric and DP-06's ownership/visibility contract before changing CRM queries.
- [x] Add first-party contact and organization list/detail/create/edit/archive/delete workflows under the `Crm/*`
  namespace.
- [x] Enforce owned/assigned visibility for Sales/Ops users, with the documented admin and Super Admin bypasses.
- [x] Add policy, filtering, pagination, validation, and direct-URL authorization tests for the new scopes.

### Task 9 — Finish Audit Coverage And Notification Policy

- [x] Ratify DP-08 and DP-09: visitor confirmation/scheduling behavior, deployment alert recipients, retention period,
  redacted fields, and searchable audit fields.
- [x] Complete audit coverage for notes, CRM activities, Horizon queue mutations/failures, and sensitive settings
  changes.
- [x] Add explicit audit filtering and unauthorized-access tests for audit and settings screens.
- [x] Reconcile the configured activity cleanup period with the ratified 365-day retention policy and schedule daily
  cleanup.

### Task 10 — Remove Starter Residue And Add Browser Smoke Coverage

- [x] Remove the fake Create/Manage team controls from `TeamsMenu.vue` and replace them with a real single-workspace
  identity control.
- [x] Add browser smoke coverage for public marketing/intake, login, dashboard, CRM lists, admin users and roles, jobs,
  settings, and audit.
- [x] Include the expressive form controls in smoke coverage for validation, dark mode, keyboard focus, and custom
  select behavior.

### Task 11 — Ratify Deployment And Complete Launch Documentation

- [x] Resolve DP-10 by selecting Laravel Forge-managed staging/production sites, the environment model, worker/scheduler
  supervision, backup/restore, error tracking, and operational ownership.
- [x] Add ADRs for decisions that changed the roadmap defaults.
- [x] Add admin/user documentation for CRM lifecycle, user management, roles, permissions, jobs, and audit review.
- [x] Update the phase documents and this roadmap after each task lands so completed work does not remain falsely
  unchecked.

### Task 12 — Restore CRM Detail Browser Smoke Coverage

- [ ] Diagnose why Pest's temporary browser HTTP server blank-renders CRM lead, organization, and contact detail URLs
  while list pages and the other browser smoke flows render normally.
- [ ] Reinstate focused CRM browser coverage that separately verifies View link navigation and the linked detail-route
  response for leads, organizations, and contacts.
- [ ] Run the browser suite and full quality contract after the detail-route rendering issue is resolved.

### Task 13 — Resolve Larascan Findings And Harden Security Gates

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

### Task 14 — Make Landing QA And Feedback A First-Class Workflow

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

### Task 15 — Resolve Landing Review Follow-ups

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

### Task 16 — Rework Public Privacy Presentation

The privacy-page testing record asks whether the policy should remain a scrollable page or become an on-demand surface.
Resolve that presentation choice before implementation; this is a UX follow-up, not a change to the ratified legal scope.

- [ ] Decide whether the privacy policy remains a dedicated page or becomes a reusable modal/dialog available from public
  pages; preserve a directly addressable policy URL either way.
- [ ] If a modal/dialog is selected, load the policy content lazily and keep keyboard, screen-reader, focus, and deep-link
  behavior accessible.
- [ ] If the page remains, reduce or remove unnecessary scrolling while preserving readable legal-document spacing and
  responsive behavior.
- [ ] Add focused coverage for the selected presentation and its public navigation entry points.

### Task 17 — Brand And Theme Shared Error Pages

User testing found that the shared 403, 404, 419, 429, 500, and 503 states are not yet fully compatible with dark mode
and do not carry Southeast Code branding.

- [ ] Apply the shared light/dark theme tokens and verify readable contrast across every supported error state.
- [ ] Add Southeast Code branding, useful recovery copy, and the appropriate navigation or home action without exposing
  debug details.
- [ ] Verify the error-page variants at responsive breakpoints and add focused coverage for status-specific rendering.

### Task 18 — Refine Audit Log Filters

User testing found three audit-screen issues: the search placeholder overlaps the magnifying-glass icon, the Subject
model-or-ID field is too verbose/truncated, and Event, Causer, and Subject should offer constrained multi-select options.

- [ ] Fix the Search activity field spacing so its placeholder and icon never overlap in supported themes and widths.
- [ ] Shorten the Subject model-or-ID label/placeholder while keeping the filter's meaning clear.
- [ ] Replace the Event, Causer, and Subject free-text filters with accessible multi-select inputs populated only from
  valid available options.
- [ ] Preserve server-side filtering, authorization, and empty-state behavior; add focused coverage for the new filter
  combinations.

### Task 19 — Repair User Invitation And Verification Flows

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

### Task 20 — Simplify Role Editing And Super Admin Presentation

User testing requested a role-first editor with one larger role selector and a dynamically loaded permission list, plus
an informational treatment for the non-editable Super Admin role.

- [ ] Replace the all-roles permission lists with a prominent accessible role selector and load the selected role's
  permissions into one focused list without weakening server-side authorization.
- [ ] Render the Super Admin permissions area as a compact info banner stating that all permissions are granted by the
  server-side Gate bypass and that the role is not editable from the UI, with an info icon on the left.
- [ ] Preserve guarded role/permission behavior, loading, empty, and error states; add focused coverage for role
  selection, permission updates, and Super Admin non-editability.

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
