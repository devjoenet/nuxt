## Resolved Decision Points

These items are already resolved by the roadmap once removed from `./active-decision-points.md`

- Southeast Code internal operating app first; reusable starter-kit polish second.
- Single Southeast Code workspace for v1; multi-company tenancy is later.
- Client portal, proposals, contracts, invoices, payments, advanced reporting, campaigns, and knowledge base are later.
- Public lead intake creates first-party CRM records.
- CRM v1 uses `Organization`, `Contact`, `Lead`, `Activity`, `Note`, and `Task`.
- `Lead` carries opportunity/pipeline progress in v1; a separate `Opportunity` model is not part of the default path.
- Pipeline/status values are code-owned PHP enums for v1.
- Local/simple queue defaults use database queues; staging/production use Redis plus Horizon.
- Authorization is enforced server-side with Laravel policies, gates, and middleware.
- Frontend links/forms use named routes and Wayfinder-generated imports.
- Pest feature tests are required for shipped workflows and authorization boundaries.
- Task 8 v1 success metric is operational CRM readiness: every inbound opportunity becomes a first-party record that
  authorized staff can locate, assign, manage, and advance. The minimum launch gate is public intake plus lead,
  contact, organization, ownership, admin-permission, jobs, and audit visibility workflows.
- Task 8 CRM ownership uses one nullable `owner_id` per CRM record. Sales/Ops users see records they own or unassigned
  records awaiting claim; Admin and Super Admin users bypass ownership filters. Only Admin and Super Admin users may
  transfer ownership. Out-of-scope direct URLs return `404`.
- Public intake identity is isolated: every anonymous submission creates a new unowned Contact and Lead, never reuses
  or mutates an existing Contact/Organization, remains unlinked from an Organization until staff resolution, and keeps
  the submitted identity/company values in an immutable Lead snapshot.
- Lead history preserves the identity of a soft-deleted Contact by loading the trashed relationship, exposing its
  deleted state, and rendering historical values without a broken Contact detail link.

## Resolved Decisions

| ID    | Decision                                                                | Why It Matters                                                                                                                      | Required Resolution                                                                                                                                        | Default If Unanswered                                                                                                                                              |
|:------|:------------------------------------------------------------------------|:------------------------------------------------------------------------------------------------------------------------------------|:-----------------------------------------------------------------------------------------------------------------------------------------------------------|:-------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| DP-02 | What is the final registration and account lifecycle?                   | Fortify registration exists, but an internal operations app usually wants invite-first staff access.                                | Decide whether public registration remains, is disabled after invites, or creates a restricted client/user role. Define deactivation/session behavior.     | Move to invite-first staff access after admin user management exists; no default role for public self-registration.                                                |
| DP-03 | Do clients log in during v1?                                            | Client users affect roles, policies, navigation, onboarding, privacy, and testing.                                                  | Confirm whether `Client Viewer` is an active v1 role or a reserved later role. Define what a client can see if active.                                     | Client portal and client login are deferred; `Client Viewer` may be seeded but receives no active portal surface.                                                  |
| DP-04 | Are there CRM model exceptions to the default v1 model?                 | Separate opportunity/project records, individual clients, and conversion output change migrations, actions, policies, and UI.       | Decide whether to add a separate `Opportunity` model, whether contacts can become clients without organizations, and whether conversion creates a project. | Use one `Lead` pipeline model; allow individual contacts; conversion marks organization/contact as client without creating projects.                               |
| DP-05 | Should pipeline/status values remain code-owned or become configurable? | Configurable stages require settings UI, validation, ordering, audit logging, and migration strategy.                               | Choose PHP enums only, database-backed stages/statuses, or a hybrid. Define who can edit them and how history is preserved.                                | Use PHP enums for v1; revisit admin-editable stages after the CRM proves useful.                                                                                   |
| DP-07 | What exactly does the public intake form collect?                       | Fields affect validation, database columns, privacy copy, spam controls, notification content, and lead quality.                    | Choose required/optional fields, consent copy, attachment support, attribution fields, and spam controls.                                                  | Require name, email, project details, and consent; optional company, phone, website, budget, timeline, service interest, referral source; no attachments.          |
| DP-08 | Who gets notified, through which channels, and when?                    | Notifications affect queues, mail setup, failed-job handling, business expectations, and tests.                                     | Define internal recipients, visitor confirmation behavior, scheduling-link behavior, reminder cadence, and channels for failed jobs/long waits.            | Internal lead alerts and follow-up reminders use the business notification email on the mail queue; visitor confirmations and scheduling links stay deferred; Horizon failed-job/long-wait alerts use HORIZON_NOTIFICATION_EMAIL. |
| DP-09 | What audit retention and redaction rules are required?                  | Audit choices affect storage growth, searchable fields, privacy risk, and what admins may inspect or delete.                        | Define logged models/events, redacted fields, retention period, searchable filters, and whether audit entries can be deleted.                              | Retain audit records for 365 days with the scheduled cleanup command; log CRM/admin/settings/queue events; redact secrets, auth material, note bodies, contact email/phone values, notification recipients, and exception messages; search description, log/event, causer, subject, and date range. |
| DP-10 | What is the deployment target and production operations model?          | Hosting determines PHP/Node availability, Redis, workers, scheduler, backups, logs, and asset build strategy.                       | Choose hosting, environment names, migration strategy, worker/scheduler supervision, Redis availability, backup policy, and error tracking.                | **Resolved:** Laravel Forge-managed staging and production sites with PHP 8.5, MySQL, Redis, Vite builds, Supervisor-managed Horizon workers, and Forge scheduler jobs; zero-downtime release macros with `optimize` and `migrate --force`; daily encrypted backups to a configured storage provider plus isolated restore tests; Forge logs plus `/up` health monitoring; Southeast Code Operations Lead owns incidents, with the Technical Maintainer as escalation. |
| DP-11 | What frontend component standard should v1 follow?                      | Mixed component systems can create duplicate form/table/dialog patterns and inconsistent accessibility.                             | Decide Nuxt UI first, local primitives first, or a strict hybrid. Define table, drawer/modal, toast, confirmation, and mobile behavior.                    | Use Nuxt UI where it fits; create local wrappers for repeated CRM/admin form, table, dialog, and status patterns.                                                  |
| DP-12 | Are privacy/legal pages required before public lead capture launches?   | Public intake may collect personal and business information, which can require privacy terms, retention copy, and consent language. | Decide required privacy policy, terms, consent wording, retention statements, and whether marketing footer links are mandatory.                            | Add privacy policy and basic contact-data consent before launch if public intake is enabled.                                                                       |
| DP-13 | How should public and staff CRM identity resolution avoid ownership leaks? | Global email/name upserts let anonymous or restricted actors mutate or attach records outside their visibility boundary.          | Define public deduplication, immutable intake evidence, staff matching identifiers, and the point at which an Organization is associated.                  | **Resolved:** isolate every public submission as a new unowned Contact/Lead with an immutable identity/company snapshot and no Organization; staff resolve only visibility-scoped UUIDs. |
| DP-14 | What should a Lead display after its Contact is soft-deleted?            | Hiding the related Contact breaks Lead serialization, while linking to its normal detail route produces a 404.                      | Choose historical display or a nullable missing-contact fallback, including the resource/type/link behavior.                                               | **Resolved:** load the Contact with trashed records, expose its deleted state, show historical identity, and omit the unusable Contact detail link.                 |

## Previously Completed Tasks

### Tasks completed prior to creating the current development task process.

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

`docs/development/active-decision-points.md` tracks the unresolved choices that can still change implementation details. The roadmap
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
- [x] DP-13: isolate anonymous identity from CRM deduplication and ownership. Every public submission creates a new
  unowned Contact/Lead, stores an immutable submitted identity/company snapshot, creates no Organization, and leaves
  staff resolution to visibility-scoped UUID selection.
- [x] DP-14: preserve Lead history after Contact deletion. Leads load soft-deleted Contacts, display the historical
  identity with a deleted state, and do not offer a Contact detail link that normal route binding cannot resolve.

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
- [x] Keep app-facing copy consistent with current Southeast Code positioning and shipped UI copy.
- [x] Keep app-facing colors aligned with the current CSS theme tokens, while meeting contrast requirements in forms,
  tables, navigation, and status badges.
- [x] Keep the UI practical and operational: dense, scannable, restrained, and built for repeated daily use.
- [x] After implementation, run the relevant verification commands:
  - `composer test`
  - `composer test:types`
  - `composer test:lint`
  - `npm run build`
  - targeted checks such as `php artisan test --compact --filter=ContactFormTest`


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

### Task 12 — Resolve Larascan Findings And Harden Security Gates

The first Larascan run reported 54 passed, 22 failed, and 12 skipped checks, with a reported highest severity of
`CRITICAL`. Treat this as a triage task rather than a blind fix list: several failures are false positives caused by
Larascan not recognizing Laravel 13 native attributes, Fortify's configured limiters, or controller middleware
attributes. Do not add a blanket baseline or lower the CI fail threshold until each failure has an owner and disposition.

- [x] **Fix the scan integration first.** Change `.github/workflows/larascan.yml` from PHP 8.4 to PHP 8.5, copy and key
  `.env.example` before booting Artisan, prepare the SQLite database if the scan requires application boot, and add a
  clean CI run that proves the scan itself is executable. Keep the existing `fail_on=high` gate.
- [x] **Remove or gate newly exposed diagnostic tooling.** Review why `garygreen/pretty-routes` and
  `jaydeep/laravel-time-machine` were added to production `require` dependencies. Remove them if they were incidental;
  otherwise move them to development-only scope, disable their dashboards outside local development, protect any local
  routes, ensure `storage/time-machine/` is ignored and empty from source control, and add production assertions that
  `/routes` and `/time-machine` are unavailable.
- [x] **Close the authentication findings.** Add focused tests for repeated failed login and 2FA challenge requests,
  confirm the custom `LoginRequest` limiter and Fortify `two-factor` limiter protect the POST flows, and decide whether
  the password-confirmed 2FA management mutations need an additional explicit throttle. Do not throttle harmless GET
  screens solely to satisfy a static rule. Add a narrow documented Larascan suppression for the GET/static false
  positives only after the runtime behavior is covered.
- [x] **Resolve the model and generated-secret findings safely.** Audit the three `forceFill()` call sites individually:
  use explicit attribute assignment where it improves mass-assignment clarity, retain a bypass only for intentional
  internal state changes, and cover password reset, admin 2FA reset, and reminder persistence with regression tests.
  Replace the admin invitation's generated password seed with `Str::password()`; keep remember tokens as tokens rather
  than treating them as passwords. Record narrow suppressions for intentional internal writes instead of suppressing
  the rule globally.
- [x] **Record existing Laravel controls as verified false positives.** `User` already hides password data and uses the
  `hashed` cast; the parameterized email-verification endpoint already has `signed` and `throttle` middleware; and the
  custom security-headers middleware already emits `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy`,
  and production HSTS. Add serialization, invalid-signature, and runtime-header assertions, then use the scanner's
  supported narrow ignore/baseline mechanism with file/rule justification so these findings do not obscure real
  regressions.
- [x] **Harden environment and session profiles.** Make `.env.example` scanner-visible and secret-free for the full
  supported key set, including queue, Redis, mail, activity-log, Horizon, and AWS endpoint keys. Define
  separate local/testing versus staging/production expectations: production must use `APP_ENV=production`,
  `APP_DEBUG=false`, HTTPS-only session cookies, encrypted sessions, an explicit secure same-site policy, and a
  non-debug log level. Confirm whether the debug-blacklist check is supported by this Laravel version before adding
  unsupported configuration.
- [x] **Harden deployment/runtime settings.** Set S3 disk visibility explicitly to `private`; validate that the
  application does not rely on remote URL wrappers before disabling `allow_url_fopen`; disable `expose_php` in the PHP
  8.5 deployment runtime; add custom 500 and 503 error pages; and verify release security behavior through the current
  CI and staging smoke checks.
- [x] **Close repository and dependency hygiene gaps.** Add Dependabot coverage for Composer, npm, and GitHub Actions;
  add `public/.well-known/security.txt` with the approved security contact and expiry; and upgrade the current esbuild
  `0.27.7` to `0.28.1` or newer before relying on the frontend development tooling on Windows. The current advisory
  affects esbuild versions `>=0.27.3` and `<0.28.1`.
- [ Defer to Task 21 ] **Define the completion gate.** The production-profile CI scan is clean with no unresolved high/critical findings,
  accepted findings have documented rationale, diagnostic routes are unavailable outside local development, and the
  current Composer/npm audit evidence is retained with the release.


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
- [x] Move the remaining focused verification and visual-review pass to Task 14; keep the landing page testing record
  unapproved for final human review.

### Task 14 — Resolve Landing Review Follow-ups

- [x] Remove the entrance animations from the Reality, Transformation, and Services sections while preserving the hero,
  contact form, and Process section treatment.
- [x] Keep section entrance animations one-way and settled after first entry where they remain enabled.
- [x] Remove the footer's `Use the contact form` text link and retain the contact icon action.
- [x] Remove the filled multi-select background seam and preserve the existing field shell treatment.
- [x] Change the estimated budget field to a text input with comma-separated currency formatting, an explicit placeholder,
  and server-side normalization/validation.
- [x] Run the focused feature tests, frontend typecheck, lint, production build, and direct Chrome review across the
  changed landing states. The temporary Pest browser server still reports its pre-existing keyboard-focus/
  `ResizeObserver` harness failure; the public testing record remains unapproved for human review.

### Task 15 — Rework Public Privacy Presentation

- [x] **Decision:** Normal activation of public privacy links opens a lazily loaded, scrollable modal. `/privacy` remains
  the canonical full-page URL and progressive fallback. This keeps the public form usable without forcing policy-page
  scrolling while preserving direct access, search indexing, modified-click behavior, and no-JavaScript navigation.
- [x] Extract the ratified policy copy into `PrivacyPolicyContent.vue`, reuse it on the canonical page and modal, and
  preserve the Wayfinder-generated contact-form link.
- [x] Add `PrivacyDialog.vue` using Nuxt UI's overlay-backed modal with a constrained scroll region, accessible title and
  description, explicit `aria-modal`, focus restoration, Escape/outside dismissal, close-before-navigation behavior, and
  a full-page fallback link.
- [x] Add `PrivacyDialogLink.vue` with a real `/privacy` anchor, activation-only dynamic import, overlay destruction after
  leave, modifier/middle-click preservation, loading state, and import-failure fallback navigation.
- [x] Replace the footer and consent entry points while preserving form state, and retain `/privacy` route/component/
  canonical metadata behavior.
- [x] Add feature and browser coverage for the direct route, consent and footer triggers, focus restoration, no URL change,
  deep-link navigation, mobile overflow, and JavaScript errors.
- [x] Verify the lazy boundary in `public/build/manifest.json`: `PrivacyDialog.vue` is listed under the landing bundle's
  `dynamicImports`, while the extracted `PrivacyPolicyContent` is a separate chunk.

### Task 16 — Brand And Theme Shared Error Pages

User testing found that the shared 403, 404, 419, 429, 500, and 503 states were not fully compatible with dark mode
and did not carry Southeast Code branding.

- [x] Apply shared light/dark theme tokens with accessible local contrast pairings across every supported error state.
- [x] Add Southeast Code branding, useful recovery copy, and context-aware navigation without exposing debug details.
- [x] Render the same branded `ErrorPage` for direct documents and Inertia requests while preserving supported status codes;
  unsupported statuses continue through Laravel's normal handling.
- [x] Verify status-specific rendering, shared authentication props, responsive layout, dark mode, accessibility, browser
  console cleanliness, and narrow-viewport overflow with focused feature and browser coverage.
- [x] Run formatting, PHPStan, typecheck, lint, production build, launch-readiness, focused error-page tests, and
  `git diff --check`.

### Task 17 — Refine Audit Log Filters

User testing found three audit-screen issues: the search placeholder overlapped the magnifying-glass icon, the Subject
model-or-ID field was too verbose/truncated, and Event, Causer, and Subject needed constrained multi-select options.

- [x] Fix the Search activity field spacing with a shared, direction-safe leading-slot label rule across themes and widths.
- [x] Shorten the Subject field label to `Subject` and its empty state to `All subject types` while preserving subject-type filtering semantics.
- [x] Replace the Event, Causer, and Subject free-text filters with accessible multi-select inputs populated from valid audit-history options; Subject selects model types, not individual records.
- [x] Preserve server-side filtering, authorization, pagination, legacy scalar-event compatibility, and empty-state behavior; add focused feature and browser coverage for array combinations, null sentinels, unknown selections, responsive geometry, themes, keyboard dismissal, and overflow.

Completion note: Task 17 treats Subject as a constrained multi-select of distinct `subject_type` classes. Verification passed with the focused feature and browser suites, frontend typecheck/lint/format/build, PHPStan, Pint, and `git diff --check`.

### Task 18 — Repair User Invitation And Verification Flows

User testing found that the invitation form sent a password-reset email without explaining the actual onboarding step,
the resend-confirmation action generated a link that returned 404, and the 2FA reset action shown for a user pending
email verification had no effect.

- [x] Add a dedicated queued Southeast Code invitation notification that explains account setup, password selection,
  email verification, token expiry, and the single setup action while preserving Laravel's ordinary self-service reset
  notification.
- [x] Move invitation, verification resend, and 2FA reset state transitions into typed single-purpose actions; keep
  invitation creation, role assignment, password-broker token creation, and audit behavior atomic and authorized.
- [x] Verify the delivered framework verification URL against the named route, configured host, internal user key, email
  hash, signature, and expiry; cover successful, guest, wrong-user, tampered, expired, and already-verified behavior.
- [x] Expose 2FA state without secrets, make reset unavailable with an explanatory disabled control when 2FA is not
  configured, and preserve accessible processing/error feedback for available admin actions.
- [x] Add focused feature and browser regressions for invitation mail, authorization, verification links, resend/reset
  transitions, audit entries, disabled UI state, and JavaScript/console cleanliness.

Completion note: Task 18 verification passed with 35 focused tests and 214 assertions, the focused admin browser test,
frontend typecheck/lint/format/build, focused Rector, max-level PHPStan, Pint, and `git diff --check`. The repository's
full `composer test` contract reached its coverage-gated test phase but could not complete because this PHP runtime has
no coverage driver; the full browser contract also exposed a LaunchSmoke focus-order failure outside Task 18, and
the full lint contract retains unrelated pre-existing Rector dry-run findings outside this task.
### Task 19 — Simplify Role Editing And Super Admin Presentation

- [x] Replace the all-roles permission lists with one accessible large role selector and a focused permission list while
  preserving server-side authorization, the existing route, Wayfinder form binding, and native checkbox submission.
- [x] Move role permission updates behind `RolePermissionsData` and `UpdateRolePermissions`, including web-guard,
  distinct-ID validation, exact replacement and clearing behavior, cache-aware `syncPermissions()`, audit logging, and
  action-level Super Admin protection.
- [x] Render the protected Super Admin state as the required compact info alert, retain explicit role/catalog/loading/
  validation states, keep role switching disabled during submission, and preserve accessible responsive light/dark UI.
- [x] Add focused feature coverage for payloads, replacement, clearing, invalid/duplicate/other-guard IDs,
  authorization, audit properties, protected immutability, and Gate bypass behavior, plus browser coverage for role
  selection, persistence, keyboard interaction, responsive themes, accessibility, and browser cleanliness.

Completion note: Task 19 focused verification passed with 9 feature tests and 60 assertions, 2 role-editor browser tests
and 24 assertions, frontend typecheck/lint/format/build, Herd-backed coverage-gated Unit/Feature tests with 144 tests,
939 assertions, and 87.9% total coverage, max-level PHPStan, Pint, and `git diff --check`. The repository's direct
`composer test` command could not run its coverage stage because this PHP runtime has no coverage driver; the full
browser contract exposed an unrelated LaunchSmoke focus-order failure, and the full lint contract retains four
unrelated pre-existing Rector dry-run findings outside this task.

### Task 20 — Make The Organization Owner Filter Clearable

- [x] Add a page-local, keyboard-reachable clear action beside the organizations-index owner filter for Admin and Super
  Admin users while keeping real user IDs as the only select options.
- [x] Return the owner filter to `All owners` through the select's empty model state, omit the empty `owner` query value,
  and preserve the existing search, debounced Inertia visit, Wayfinder URL, state-preservation, and history-replacement
  behavior.
- [x] Keep the filter group full-width on narrow screens without changing organization ownership, server visibility,
  query behavior, schema, dependencies, or the shared `FormSelect` API.
- [x] Add focused browser coverage for owner selection, scoped results, URL state, accessible clearing, restored results,
  JavaScript and console cleanliness, and 390-pixel viewport behavior.

Completion note: Task 20 verification passed with the 10-test CRM entity feature suite and 98 assertions, the focused
organization-filter browser test with 29 assertions, frontend typecheck/lint/format/build, both PHPStan levels, Pint,
and Herd-backed Unit/Feature coverage with 144 tests, 941 assertions, and 87.9% total coverage. The repository's direct
`composer test` command could not run its coverage stage because this PHP runtime has no coverage driver; the full
browser contract retained the unrelated LaunchSmoke focus-order failure, and the full lint contract retained four
unrelated pre-existing Rector dry-run findings outside this task.

### Task 25 — Type CRM Filters And Scope Related Counts

- [x] Move Organization, Contact, and Lead index input through authorized Form Requests and immutable typed DTOs,
  including enum-backed sorting, active-owner validation, UUID and Lead-enum validation, and normalization of
  false-like archive input before query construction.
- [x] Make CRM index queries accept only the authenticated user and their typed DTO, retain existing pagination and
  Inertia filter props, and use one archive comparison with stable enum defaults.
- [x] Apply the existing `visibleTo($user)` scope to Organization contact/lead and Contact lead count subqueries for
  both indexes and details, retaining administrator totals naturally.
- [x] Add feature regressions for typed/invalid/array filter input, trimmed search, false-like archive input,
  restricted-user counts, administrator totals, resource payloads, and direct-route visibility.

Completion note: Task 25 verification passed with 26 focused CRM feature tests and 361 assertions, max-level PHPStan,
frontend typecheck, Pint, and `git diff --check`. The Herd-backed Unit/Feature coverage suite passed with 152 tests,
1,168 assertions, and 90.5% coverage. The direct `composer test` command reached its coverage stage but could not
complete because the shell PHP runtime has no coverage driver.

### Task 26 — Type And Scale The Audit Query Boundary

- [x] Add a reversible named B-tree index for `activity_log.created_at`; keep 365-day cleanup authoritative.
- [x] Move authorized audit filtering through `AuditIndexRequest` and immutable `ActivityLogFiltersData`, preserving
  special audit tokens, parameter binding, eager loading, ordering, and Inertia filter props.
- [x] Cache only deterministic scalar audit option catalogs for five minutes under a versioned key.
- [x] Add Query `build()` signature architecture coverage and focused validation, cache-expiry, index, feature, and
  browser regressions.

Completion note: The local MySQL audit table had 158 rows. Before the migration, latest and date-range `EXPLAIN` plans
used a full scan/filesort. The named index is now present; a representative one-day range uses it with a backward index
scan over 10 rows. Focused audit feature (9 tests, 102 assertions), architecture (4 tests, 95 assertions), and browser
(1 test, 24 assertions) suites passed. Migration apply/rollback/reapply, PHP lint, Pint, max-level PHPStan, frontend
typecheck/build, Herd coverage (156 tests, 1,219 assertions, 90.7%), and `git diff --check` passed. The direct
`composer test` contract reached its coverage-gated phase but cannot complete in the shell PHP runtime because it lacks
a coverage driver.

### Task 21 — Redesign The Organization And Contact Detail Workspaces And Repair Restore

- [x] Add a presentation-only CRM detail shell with responsive summary, main-content, and aside slots while preserving
  each entity page's routes, authorization, state, and lifecycle behavior.
- [x] Rework the organization and contact workspaces around one clear heading, relationship context, semantic detail
  lists, resilient absent-value states, and wrapping mobile layouts in both color modes.
- [x] Correct archived-record restore controls to issue local Inertia DELETE requests while preserving POST archive and
  DELETE destroy behavior.
- [x] Extend feature and browser coverage for direct-route scoping, Resource props, lifecycle permissions, DELETE
  restore, contact-empty state, responsive light/dark layouts, overflow, and browser cleanliness.

Completion note: Task 21 verification passed with 11 focused CRM entity feature tests and 124 assertions, 5 focused
CRM detail browser tests and 64 assertions, frontend typecheck/lint/format/build, Herd-backed Unit/Feature coverage
with 145 tests, 967 assertions, 88.6% total coverage, the full browser contract, lint, PHPStan, and max-level PHPStan.
The direct `composer test` command reached its coverage-gated test phase but could not complete because the shell PHP
runtime has no coverage driver; the equivalent Herd-backed coverage suite passed. Organization and contact detail
user-testing records are handed back as Needs Approval.

### Task 22 — Preserve Lead History When A Contact Is Deleted

- [x] Preserve soft-deleted Contacts through the Lead relationship and expose their deletion state without weakening
  normal Contact visibility.
- [x] Render historical identity with an explicit deleted state and no unusable Contact detail link.
- [x] Keep inaccessible Contact data out of Lead payloads and cover active, deleted, and hidden relationship states.

Completion note: Task 22 verification passed 25 focused feature/browser tests with 300 assertions, frontend
typecheck/lint/format/build, Pint, and `git diff --check`. Herd-backed Unit/Feature coverage passed with 146 tests,
998 assertions, and 88.6% coverage. The direct `composer test` command reached its coverage phase but could not
complete because the shell PHP runtime has no coverage driver.

### Task 23 — Mark Follow-Up Reminders Sent Only After Delivery

- [x] Run reminder jobs on the explicit `mail` queue and deliver the mail channel synchronously within the job.
- [x] Stamp `reminder_sent_at` only after successful delivery and let failures bubble so the unique job can retry.
- [x] Remove the notification's second queue boundary and cover success, idempotency, delivery failure, and retry state.

Completion note: Task 23 verification passed 6 focused jobs/operations tests, Pint, and `git diff --check`.
Herd-backed Unit/Feature coverage passed with 147 tests and 89.3% coverage. The direct `composer test` command could
not complete because the shell PHP runtime has no coverage driver.

### Task 24 — Isolate Intake Identity And Bound CRM Record Selectors

- [x] Make every anonymous submission create a new unowned Contact and Lead with an immutable intake snapshot and no
  Organization matching or creation.
- [x] Resolve staff Contact and Organization associations by UUID through visibility-scoped queries that conceal
  inaccessible records.
- [x] Add bounded, visibility-scoped search endpoints and async selectors with selected-value retention and explicit
  create-new paths.

Completion note: Task 24 verification passed both migrations, 36 focused feature/browser tests with 293 assertions,
Pint, max-level PHPStan, frontend typecheck/lint/format/build, the lint contract, and `git diff --check`. The direct
`composer test` command passed type coverage and then stopped because the shell PHP runtime has no coverage driver.

### Task 27 — Move Coherent State Changes Into Actions

- [x] Move coherent CRM lifecycle, note, follow-up, admin-user, and business-setting state changes into focused typed
  Actions and DTOs.
- [x] Keep HTTP authorization and validation at the transport boundary while Actions own reusable invariants,
  transactions, audit effects, and persistence.
- [x] Centralize CRM visibility constraints used by relationship counts/eager loading and add direct Action and domain
  boundary coverage.

Completion note: Task 27 verification passed 178 Unit/Feature tests with 1,537 assertions and 100% measured coverage,
30 browser tests with 298 assertions, normal and maximum PHPStan, 100% type coverage, Pint, Rector, Prettier, ESLint,
Vue typecheck, the production build, the production npm audit, and `git diff --check`.

### Task 28 — Enable Strict Frontend Lint Guardrails

- [x] Remove the unused `useFillInObjects` composable, its unsafe `Record<any, unknown>` baseline, and stale generated
  auto-import declarations after confirming there were no live callers.
- [x] Remove the unused `UserMenu` `chip-leading` slot and its unsafe template assertion after confirming there were
  no implementation-time consumers.
- [x] Enable `@typescript-eslint/no-explicit-any` and `@typescript-eslint/no-unused-vars` as errors, with only the
  narrow underscore exceptions for intentionally unused values.
- [x] Keep frontend lint and typecheck as separate scripts and required CI steps.

Completion note: Task 28 verification passed strict ESLint, `npm run typecheck`, `npm run lint:check`,
`npm run format:check`, `npm run build`, Herd-backed Unit/Feature coverage with 178 tests, 1,537 assertions, and
100.0% coverage, the full browser contract with 30 tests and 298 assertions, `composer test:lint`,
`composer test:types`, `composer test:types:max`, and `git diff --check`. The direct `composer test` command reached
its coverage-gated phase but could not complete because the shell PHP runtime has no coverage driver.

### Task 29 — Reconcile User-Testing And Deployment Documentation Contracts

- [x] Render completed mapped user-testing tasks as Needs verification while preserving persisted review, roadmap,
  and approval state.
- [x] Replace obsolete Larascan deployment instructions with current Composer and production npm audit evidence.
- [x] Add filesystem and Artisan command contracts for maintained documentation and reconcile the active roadmap with
  completed task records.

### Task 30 — Harden CRM Role And Ownership Visibility

- [x] Remove CRM access from the reserved Client Viewer role and gate CRM navigation by the shared permission map.
- [x] Scope dashboard lead and follow-up aggregates/lists, related Resources, relationship search, and UUID filters by
  the existing CRM ownership boundary.
- [x] Add role, migration, administrator-bypass, direct-route, dashboard, resource, search, filter, and browser
  regressions for mismatched owners.

### Task 31 — End Deactivated Sessions At Fortify Boundaries

- [x] Add EnsureUserIsActive to Fortify's configured route middleware so authenticated password-confirmation and
  two-factor management endpoints terminate deactivated sessions.
- [x] Preserve guest challenge, active-user, password-confirmation, validation, and throttling behavior.
- [x] Add a route-registry and dataset-driven HTTP matrix covering every sensitive Fortify endpoint and two-factor
  state preservation.

### Task 32 — Preserve User-Testing State And Rename Its Permission

- [x] Move user-testing state transitions behind an immutable DTO and transaction-safe, lock-and-reload Action that
  preserves actors and timestamps across repeated saves.
- [x] Rename the capability in place to development.user_testing.manage while retaining existing role assignments.
- [x] Add frozen-time Action, HTTP transition, permission migration, validation, authorization, and queue-removal
  regressions.
