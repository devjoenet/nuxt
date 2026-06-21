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
- [ ] CRM screens still use external Nuxt demo APIs instead of first-party data.
- [ ] No first-party CRM tables, models, controllers, policies, factories, or seeders exist yet.
- [ ] `User` is not wired to Spatie roles/permissions.
- [ ] Horizon/Horizon UI access is not protected by business roles or permissions yet.
- [ ] Queue intent is split: local `.env.example` defaults to database queues while Horizon is configured for Redis.
- [ ] README and CI still contain stale version/app-positioning claims that do not match the current stack.

## Open Decisions To Ratify

`docs/00-decision-points.md` tracks the unresolved choices that can still change implementation details. The roadmap
defaults above remain the executable path until those decisions are made.

- [ ] DP-01: choose the v1 success metric and launch gate.
- [ ] DP-02: confirm the final registration/account lifecycle after invite-based admin management exists.
- [ ] DP-03: decide whether clients ever log in during v1 or whether client access is explicitly deferred.
- [ ] DP-04: confirm CRM model exceptions: separate opportunity records, individual clients, and conversion output.
- [ ] DP-05: confirm whether pipeline/status values stay code-owned for v1 or become admin-configurable.
- [ ] DP-06: define CRM ownership visibility for sales/ops users.
- [ ] DP-07: lock the public intake field set, consent copy, and spam controls.
- [ ] DP-08: choose notification recipients, channels, and scheduling-link behavior.
- [ ] DP-09: define audit retention, redaction, and searchable fields.
- [ ] DP-10: confirm deployment target and production queue/Horizon operations.
- [ ] DP-11: standardize the UI component approach and responsive table behavior.
- [ ] DP-12: decide privacy/legal page requirements for public lead capture.

## Roadmap Checklist

### Foundation Cleanup

- [ ] Update README/package messaging to Laravel 13, PHP 8.5, Inertia 3, and Southeast Code operating-app direction.
- [ ] Align CI PHP version with Composer's PHP 8.5 requirement.
- [ ] Remove or replace demo Nuxt dashboard references, placeholder GitHub links, fake teams, and external demo API
  calls.
- [ ] Define navigation groups for public marketing, authenticated workspace, admin/ops, settings, jobs, and audit.
- [ ] Establish route/page naming conventions for marketing, CRM, admin, settings, jobs, and audit sections.
- [ ] Rename `/customers` and the `Customers.vue` page to the CRM route/page namespace before building real data behind
  it. Default route group: `/crm`, route names: `crm.*`, page namespace: `Crm/*`.

### Marketing Site Bones

- [ ] Finalize the one-page customer-facing flow: hero, services, process, proof/metrics, trust signals, and contact
  CTA.
- [ ] Add a first-party contact/intake form that persists submissions as CRM leads.
- [ ] Add thank-you state, internal notification, and basic spam/rate-limit protection.
- [ ] Add SEO metadata, social image, sitemap/robots review, and accessible page structure.
- [ ] Keep marketing content code-owned for v1; move admin-editable marketing content to later work unless the roadmap
  changes.
- [ ] Add a focused test proving a visitor can submit the contact form and create a lead.

### CRM MVP

- [ ] Add first-party CRM data model: organizations/accounts, contacts, leads, notes, activities, and tasks. Keep tags,
  separate opportunity records, and database-managed stages out of v1 unless a decision explicitly pulls them in.
- [ ] Add migrations, models, factories, seeders, policies, requests, resources/data objects, and controllers for the
  CRM core.
- [ ] Replace `Customers.vue` demo API data with real Inertia props/actions and server-side pagination/filtering.
- [ ] Build list, detail, create, edit, archive, and delete flows for leads, contacts, and organizations.
- [ ] Add a lead status pipeline and conversion flow from lead to client. Create project records only if DP-04 is
  resolved in favor of project creation during conversion.
- [ ] Add follow-up tasks, due dates, ownership, and activity history.
- [ ] Add realistic local seed data for leads, clients, contacts, activities, and follow-ups.
- [ ] Add focused Pest feature tests for CRM CRUD, validation, authorization, filtering, and conversion.

### Admin Section

- [ ] Add an admin dashboard with business health cards: leads, qualified pipeline leads, follow-ups due, failed jobs,
  recent activity, and recent users.
- [ ] Wire Spatie roles/permissions into `User`.
- [ ] Seed default roles and permissions.
- [ ] Enforce role/permission middleware and policies across admin, CRM, jobs, settings, and audit sections.
- [ ] Build user management: invite, deactivate, verify status, assign roles, and reset 2FA/recovery where appropriate.
- [ ] Build role/permission management with guarded access for super admins.
- [ ] Add an activity log viewer for sensitive model changes and admin actions.
- [ ] Add business settings for notification recipients, company profile, and default task rules. Keep lead statuses and
  pipeline stages code-owned/read-only unless DP-05 changes the default.

### Jobs, Queues, And Operations

- [ ] Configure Redis plus Horizon for staging/production and keep local/simple defaults aligned with database queues.
- [ ] Protect Horizon and Horizon UI using roles or permissions instead of the current empty email allow-list.
- [ ] Add queue/job admin links for failed jobs, pending jobs, batches, job search, and Horizon views.
- [ ] Add queued jobs for lead notifications and follow-up reminders. Add intake confirmations or activity digests only
  if DP-08 enables them.
- [ ] Schedule Horizon snapshots.
- [ ] Define queue wait and failed-job notification behavior.
- [ ] Add job tags for CRM, notifications, imports, exports, and operational reminders.
- [ ] Add operational runbook notes only after queue and Horizon behavior exists.

### Permissions And Audit

- [ ] Define permission names around capabilities, not pages: `crm.view`, `crm.manage`, `admin.users.manage`,
  `admin.roles.manage`, `jobs.view`, `jobs.manage`, `settings.manage`, `audit.view`.
- [ ] Hide navigation items based on permissions.
- [ ] Add policies for CRM entities and admin-only operations.
- [ ] Log create/update/delete events for CRM records, role changes, user changes, queue actions, and sensitive settings
  changes.
- [ ] Apply the default audit redaction rules from Phase 4 and update them if DP-09 changes retention/redaction policy.
- [ ] Add tests proving unauthorized users cannot see or perform admin, CRM, jobs, settings, or audit actions.

### Starter-Kit Quality

- [ ] Replace empty/demo states with useful Southeast Code product empty states.
- [ ] Add tests for marketing contact submission, CRM CRUD, permissions, Horizon/admin access, and queue dispatching.
- [ ] Add browser smoke coverage later for critical auth, marketing, CRM, and admin screens.
- [ ] Keep app-facing copy consistent with Southeast Code positioning from `docs/CompanyBrief.md`.
- [ ] Keep app-facing colors aligned with `docs/ColorPalette.md`, while meeting contrast requirements in forms, tables,
  navigation, and status badges.
- [ ] Keep the UI practical and operational: dense, scannable, restrained, and built for repeated daily use.
- [ ] After implementation, run the relevant verification commands:
  - `composer test`
  - `composer test:types`
  - `composer test:lint`
  - `npm run build`
  - targeted checks such as `php artisan test --compact --filter=ContactFormTest`

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
