# Southeast Code Roadmap

This roadmap tracks the work needed to turn the current Laravel/Inertia starter kit into a functional Southeast Code
operating app: customer-facing marketing, a basic CRM, and an admin area for users, roles, permissions, queues, jobs,
audit trails, and business operations.

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

## Decisions To Make

- [ ] Confirm product direction: Southeast Code internal operating app first, reusable starter kit second.
- [ ] Confirm CRM lifecycle names. Default: `Lead -> Opportunity -> Client -> Project`.
- [ ] Choose contact capture shape. Default: contact form plus optional intake fields.
- [ ] Decide whether to add a calendar link in v1 or keep scheduling manual until the CRM flow is proven.
- [ ] Confirm tenancy/team model. Default: one Southeast Code workspace now, client portal later.
- [ ] Choose queue backend by environment. Default: database queues for local/simple MVP, Redis plus Horizon for
  production.
- [ ] Confirm initial roles. Default: `Super Admin`, `Admin`, `Sales/Ops`, `Client Viewer`.
- [ ] Decide editable marketing content scope. Default: static code content now, lightweight admin-managed content
  later.
- [ ] Decide which operational modules are v1 versus later: proposals, contracts, invoicing, client portal, reporting,
  knowledge base.

## Roadmap Checklist

### Foundation Cleanup

- [ ] Update README/package messaging to Laravel 13, PHP 8.5, Inertia 3, and Southeast Code operating-app direction.
- [ ] Align CI PHP version with Composer's PHP 8.5 requirement.
- [ ] Remove or replace demo Nuxt dashboard references, placeholder GitHub links, fake teams, and external demo API
  calls.
- [ ] Define navigation groups for public marketing, authenticated workspace, admin/ops, settings, jobs, and audit.
- [ ] Establish route/page naming conventions for marketing, CRM, admin, settings, jobs, and audit sections.
- [ ] Decide whether `Customers` should be renamed to `CRM`, `Leads`, or `Clients` before building real data behind it.

### Marketing Site Bones

- [ ] Finalize the one-page customer-facing flow: hero, services, process, proof/metrics, trust signals, and contact
  CTA.
- [ ] Add a first-party contact/intake form that persists submissions as CRM leads.
- [ ] Add thank-you state, internal notification, and basic spam/rate-limit protection.
- [ ] Add SEO metadata, social image, sitemap/robots review, and accessible page structure.
- [ ] Decide which marketing content stays hardcoded versus admin-editable.
- [ ] Add a focused test proving a visitor can submit the contact form and create a lead.

### CRM MVP

- [ ] Add first-party CRM data model: organizations/accounts, contacts, leads/opportunities, notes, activities, tasks,
  and tags/statuses.
- [ ] Add migrations, models, factories, seeders, policies, requests, resources/data objects, and controllers for the
  CRM core.
- [ ] Replace `Customers.vue` demo API data with real Inertia props/actions and server-side pagination/filtering.
- [ ] Build list, detail, create, edit, archive, and delete flows for leads, contacts, and organizations.
- [ ] Add a lead status pipeline and conversion flow from lead/opportunity to client/project.
- [ ] Add follow-up tasks, due dates, ownership, and activity history.
- [ ] Add realistic local seed data for leads, clients, contacts, activities, and follow-ups.
- [ ] Add focused Pest feature tests for CRM CRUD, validation, authorization, filtering, and conversion.

### Admin Section

- [ ] Add an admin dashboard with business health cards: leads, open opportunities, follow-ups due, failed jobs, recent
  activity, and recent users.
- [ ] Wire Spatie roles/permissions into `User`.
- [ ] Seed default roles and permissions.
- [ ] Enforce role/permission middleware and policies across admin, CRM, jobs, settings, and audit sections.
- [ ] Build user management: invite, deactivate, verify status, assign roles, and reset 2FA/recovery where appropriate.
- [ ] Build role/permission management with guarded access for super admins.
- [ ] Add an activity log viewer for sensitive model changes and admin actions.
- [ ] Add business settings for lead statuses, pipeline stages, notification recipients, company profile, and default
  task rules.

### Jobs, Queues, And Operations

- [ ] Decide and configure the production queue backend consistently with Horizon.
- [ ] Protect Horizon and Horizon UI using roles or permissions instead of the current empty email allow-list.
- [ ] Add queue/job admin links for failed jobs, pending jobs, batches, job search, and Horizon views.
- [ ] Add queued jobs for lead notifications, intake confirmations, follow-up reminders, and activity digests.
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
- [ ] Decide which fields should be excluded or redacted from activity logs.
- [ ] Add tests proving unauthorized users cannot see or perform admin, CRM, jobs, settings, or audit actions.

### Starter-Kit Quality

- [ ] Replace empty/demo states with useful Southeast Code product empty states.
- [ ] Add tests for marketing contact submission, CRM CRUD, permissions, Horizon/admin access, and queue dispatching.
- [ ] Add browser smoke coverage later for critical auth, marketing, CRM, and admin screens.
- [ ] Keep app-facing copy consistent with Southeast Code positioning from `docs/CompanyBrief.md`.
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
