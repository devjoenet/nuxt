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
- [ ] DP-08: choose notification recipients, channels, and scheduling-link behavior. Internal lead email and queued
  follow-up notifications exist; visitor confirmation, scheduling links, and deployment-specific alert recipients are
  still deferred.
- [ ] DP-09: define audit retention, redaction, and searchable fields. Secret redaction exists, but the configured
  365-day cleanup conflicts with the documented indefinite-retention default and the searchable field set is limited.
- [ ] DP-10: confirm deployment target and production queue/Horizon operations. The repo documents an assumed model but
  no hosting target or production ownership has been ratified.
- [x] DP-11: standardize the UI component approach and responsive table behavior. Nuxt UI is the base with local
  wrappers for repeated form and workspace patterns.
- [x] DP-12: decide privacy/legal page requirements for public lead capture. A privacy page and contact-data consent are
  shipped; terms remain unnecessary unless the decision changes.

## Roadmap Checklist

### Foundation Cleanup

- [x] Update README/package messaging to Laravel 13, PHP 8.5, Inertia 3, and Southeast Code operating-app direction.
- [x] Align CI PHP version with Composer's PHP 8.5 requirement.
- [ ] Remove or replace demo Nuxt dashboard references, placeholder GitHub links, fake teams, and external demo API
  calls. Production-facing demo APIs and links are gone, but `TeamsMenu.vue` still exposes fake Create/Manage team
  actions.
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
- [ ] Log create/update/delete events for CRM records, role changes, user changes, queue actions, and sensitive settings
  changes.
- [x] Apply the default audit redaction rules from Phase 4 and update them if DP-09 changes retention/redaction policy.
- [ ] Add tests proving unauthorized users cannot see or perform admin, CRM, jobs, settings, or audit actions. Existing
  coverage proves the main admin, CRM, jobs, and guest boundaries; settings, audit, and ownership permutations still
  need explicit coverage.

### Starter-Kit Quality

- [x] Replace empty/demo states with useful Southeast Code product empty states.
- [x] Add tests for marketing contact submission, CRM CRUD, permissions, Horizon/admin access, and queue dispatching.
- [ ] Add browser smoke coverage later for critical auth, marketing, CRM, and admin screens.
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

- [ ] Ratify DP-08 and DP-09: visitor confirmation/scheduling behavior, deployment alert recipients, retention period,
  redacted fields, and searchable audit fields.
- [ ] Complete audit coverage for notes, CRM activities, queue actions, and sensitive settings changes.
- [ ] Add explicit audit filtering and unauthorized-access tests for audit and settings screens.
- [ ] Reconcile the configured activity cleanup period with the ratified retention policy.

### Task 10 — Remove Starter Residue And Add Browser Smoke Coverage

- [ ] Remove the fake Create/Manage team controls from `TeamsMenu.vue` or replace them with a real single-workspace
  identity control.
- [ ] Add browser smoke coverage for public marketing/intake, login, dashboard, CRM lead/entity flows, admin users and
  roles, jobs, settings, and audit.
- [ ] Include the expressive form controls in smoke coverage for validation, dark mode, keyboard focus, and custom
  select behavior.

### Task 11 — Ratify Deployment And Complete Launch Documentation

- [ ] Resolve DP-10 by selecting the production host, environment model, worker/scheduler supervision, backup/restore,
  error tracking, and operational ownership.
- [ ] Add ADRs for decisions that changed the roadmap defaults.
- [ ] Add admin/user documentation for CRM lifecycle, user management, roles, permissions, jobs, and audit review.
- [ ] Update the phase documents and this roadmap after each task lands so completed work does not remain falsely
  unchecked.

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
