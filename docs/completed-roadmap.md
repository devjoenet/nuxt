## Resolved Decision Points

These items are already resolved by the roadmap once removed from `./00-decision-points.md`

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
