# Application Development Decision Points

This document expands the roadmap into the decisions that should be made before or during development of the Southeast Code operating app. The goal is to reduce implementation pauses, avoid hidden product/architecture assumptions, and make later tradeoffs explicit.

## 1. Product Scope And Positioning

- Decide whether the primary product is an internal Southeast Code operating app, a reusable Laravel/Inertia starter kit, or a hybrid with clear boundaries.
- Define the v1 success metric: more qualified leads, faster lead follow-up, internal visibility, operations automation, or starter-kit reusability.
- Decide whether marketing, CRM, admin, jobs, and audit all ship together in v1 or whether some are private beta features.
- Decide whether v1 is for one Southeast Code workspace only or should be designed for future multi-company use.
- Decide what “complete enough” means for launch: lead capture only, full CRM lifecycle, admin user management, or operational reporting.
- Decide whether the application should be branded entirely as Southeast Code or keep any generic starter-kit affordances.
- Decide what existing starter-kit/demo screens should be removed, renamed, or retained as internal examples.
- Decide whether public visitors, authenticated staff, client users, and system admins are the only user groups for v1.
- Decide whether clients ever log in during v1 or whether client-portal work is explicitly deferred.
- Decide whether the product language should emphasize software development services, operations automation, CRM enablement, or all three.

## 2. Roadmap Phasing

- Decide the exact phase boundaries for foundation cleanup, marketing, CRM, admin, jobs/queues, audit, and later client portal work.
- Decide whether foundation cleanup must be completed before feature implementation starts.
- Decide which features are allowed to launch behind permissions or feature flags before their full UI is complete.
- Decide whether each phase needs its own release tag, migration checkpoint, and user acceptance review.
- Decide whether the CRM MVP should be delivered before admin role management, or whether permissions must come first.
- Decide whether operational jobs should be implemented only after CRM data exists or in parallel with marketing lead capture.
- Decide what is explicitly out of scope for v1: proposals, contracts, invoicing, payments, advanced reporting, email campaigns, knowledge base, and client portal.

## 3. Domain Model And CRM Vocabulary

- Confirm lifecycle terminology: `Lead -> Opportunity -> Client -> Project` or an alternative such as `Prospect -> Deal -> Account`.
- Decide whether `Lead` and `Opportunity` are separate tables/models or one pipeline entity with stages.
- Decide whether `Organization`, `Contact`, and `Client` are distinct concepts or whether `Organization` becomes the client record after conversion.
- Decide whether individual people can be clients without an organization.
- Decide how multiple contacts on one organization are represented and which contact is considered primary.
- Decide whether a lead can exist without an organization or contact.
- Decide whether notes, activities, and tasks are polymorphic across CRM entities.
- Decide whether tags are global, CRM-specific, user-managed, or seeded only.
- Decide how statuses and pipeline stages are stored: enums in code, database tables, or configurable settings.
- Decide whether tasks support only due dates or also reminders, recurrence, priority, assignee, and completion metadata.
- Decide whether CRM records are hard-deleted, soft-deleted, archived, or retained indefinitely.
- Decide whether conversion creates new records, changes state on existing records, or both.
- Decide whether project records are in v1 or only created as a placeholder after client conversion.
- Decide whether duplicate detection is required for contact email, organization domain, phone number, or lead source.
- Decide whether imports/exports are required in v1 or deferred.

## 4. Data Ownership And Access Control

- Decide whether CRM records have an owner, team assignment, both, or no owner in v1.
- Decide whether admins can view all CRM records while sales/ops users only view owned records.
- Decide whether users can transfer ownership of leads, contacts, organizations, tasks, and notes.
- Decide whether clients can eventually view only project/client-portal records or selected CRM history.
- Decide whether model policies are role-based only or combine roles, permissions, ownership, and record status.
- Decide whether authorization is enforced solely server-side or also mirrored in shared Inertia props for UI hiding.
- Decide how unauthorized direct URL access should behave: 403, redirect to dashboard, or not-found response.

## 5. Roles And Permissions

- Confirm initial roles: `Super Admin`, `Admin`, `Sales/Ops`, and `Client Viewer`.
- Decide whether role names are user-facing and editable or fixed system constants.
- Decide permission naming conventions around capabilities rather than pages, such as `crm.view`, `crm.manage`, `admin.users.manage`, `admin.roles.manage`, `jobs.view`, `jobs.manage`, `settings.manage`, and `audit.view`.
- Decide whether all permissions are seeded and code-owned or whether admins can create custom permissions.
- Decide whether `Super Admin` bypasses all permission checks through Spatie's `before` hook/Gate behavior.
- Decide whether role and permission changes require activity logging and notification.
- Decide whether roles are assigned during invite flow, after account activation, or both.
- Decide whether default registration remains public and what role, if any, new users receive.
- Decide whether self-registration should be disabled once invite-based admin management exists.
- Decide whether Horizon, Horizon UI, audit logs, settings, users, and roles each have separate permissions.

## 6. Authentication, Registration, And Account Lifecycle

- Decide whether public registration remains enabled for v1 or the app moves to invite-only staff access.
- Decide whether email verification is mandatory for all authenticated users.
- Decide whether two-factor authentication is optional, required for admins, or required for everyone.
- Decide whether account deactivation preserves sessions or immediately logs users out.
- Decide whether users can delete their own accounts in an internal operations app.
- Decide whether admins can reset 2FA/recovery codes, force password resets, or resend verification links.
- Decide whether password confirmation is required before sensitive admin actions.
- Decide whether login throttling defaults are sufficient for the public attack surface.
- Decide whether social login or passkeys are out of scope.
- Decide whether user profile fields need expansion beyond name/email/avatar.

## 7. Marketing Site And Lead Capture

- Decide the final one-page marketing sequence: hero, services, process, proof/metrics, trust signals, contact CTA, and footer.
- Decide whether marketing copy is code-owned, markdown/content-file based, or editable through admin settings.
- Decide whether the contact form creates a lead, contact, organization, activity, task, or all of them.
- Decide required intake fields: name, email, company, phone, website, budget, timeline, service interest, project details, referral source, and consent.
- Decide whether intake forms support attachments.
- Decide whether CAPTCHA, honeypot, rate limiting, IP throttling, or email-domain filtering is required.
- Decide whether lead source attribution includes UTM parameters, referrer, landing page, and campaign fields.
- Decide whether visitors receive an automated confirmation email.
- Decide who receives internal notifications for new leads.
- Decide whether a scheduling link appears immediately after submission.
- Decide whether SEO work includes dynamic metadata, Open Graph image generation, sitemap, robots, canonical URLs, and structured data.
- Decide accessibility targets for the public marketing page and contact form.

## 8. Admin Dashboard And Operations

- Decide which dashboard cards are v1: new leads, open opportunities, follow-ups due, overdue tasks, failed jobs, recent activity, recent users, and conversion rate.
- Decide whether dashboard metrics are real-time, cached, queued, or computed on request.
- Decide whether admins can configure dashboard date ranges.
- Decide whether operational metrics should be backed by dedicated query objects/actions.
- Decide whether failed jobs and queue status appear in the primary admin dashboard or a separate jobs area.
- Decide whether business settings are global database records, config files, or a hybrid.
- Decide whether settings changes require audit entries and who can restore previous values.

## 9. Jobs, Queues, Horizon, And Scheduling

- Decide queue backend by environment: database for local/simple MVP, Redis plus Horizon for production, or Redis everywhere.
- Decide whether Horizon is required in all deployed environments or only production/staging.
- Decide how Horizon and Horizon UI authorization is enforced: role, permission, explicit email allow-list, or combination.
- Decide queue names for CRM notifications, mail, reminders, imports, exports, reports, and default work.
- Decide worker timeout, retry, backoff, and max-exception defaults.
- Decide which jobs need tags for Horizon filtering.
- Decide whether lead notifications, intake confirmations, follow-up reminders, activity digests, and snapshots are queued in v1.
- Decide whether failed-job notifications go to email, Slack, database notifications, or all three.
- Decide scheduling frequency for Horizon snapshots, reminder scans, digest creation, and cleanup jobs.
- Decide whether long-wait detection is configured and who receives alerts.
- Decide whether local development uses sync, database, or Redis queues.

## 10. Audit, Activity Logging, And Compliance

- Decide which models are logged: users, roles, permissions, CRM records, settings, jobs, and sensitive auth/security events.
- Decide which events are logged: create, update, delete, restore, archive, assignment, conversion, role assignment, impersonation if ever added, and settings changes.
- Decide which fields are excluded or redacted from logs.
- Decide retention period for activity logs.
- Decide whether activity logs are searchable/filterable by causer, subject, event, date range, and changed field.
- Decide whether users can see audit entries related to themselves.
- Decide whether audit entries can ever be deleted by admins.
- Decide whether sensitive settings changes require before/after diffs or summarized entries only.

## 11. Frontend Architecture And UX System

- Decide whether to standardize on Nuxt UI components, local Reka-UI primitives, or a hybrid.
- Decide the layout hierarchy for public marketing, authenticated workspace, settings, admin, jobs, audit, and future client portal areas.
- Decide navigation grouping and permission-aware sidebar behavior.
- Decide whether tables use server-side pagination, sorting, filtering, and search by default.
- Decide whether CRUD screens are full pages, modals, drawers, or mixed by use case.
- Decide how form validation errors are displayed consistently across Inertia `<Form>` and custom controls.
- Decide whether floating-label inputs and Material Design 3-style interactions are implemented as reusable components before feature forms are built.
- Decide where shared TypeScript types live and how they map to Laravel resources/data objects.
- Decide whether frontend DTO/type generation is manual, Wayfinder-generated, or supported by additional tooling later.
- Decide how empty states, loading states, destructive confirmations, toast notifications, and optimistic UI are standardized.
- Decide how mobile navigation and dense CRM/admin tables behave on small screens.
- Decide whether screenshots/visual QA are required for major UI changes.

## 12. Backend Architecture And Conventions

- Decide namespace conventions for actions, DTOs/data objects, requests, resources, policies, query builders, filters, and enums.
- Decide whether controllers are invokable, resource controllers, or small conventional controllers per entity.
- Decide whether complex business operations are always single action classes.
- Decide whether data transfer uses Laravel resources, custom DTOs, Spatie Laravel Data if added later, or simple arrays.
- Decide whether status/stage values use PHP enums and how database values are validated.
- Decide whether model observers handle logging/defaults or whether explicit actions own side effects.
- Decide whether route model binding uses UUIDs for all public/admin-facing URLs.
- Decide whether database constraints mirror validation rules for uniqueness and integrity.
- Decide how seeders are split between system data, demo data, and local-only fixtures.
- Decide naming conventions for policies, permissions, and tests before implementation starts.

## 13. Database, Migrations, And Data Integrity

- Decide whether MySQL is the only supported database or whether SQLite test compatibility must be preserved.
- Decide whether CRM tables use UUID primary keys, integer primary keys with UUID columns, or default Laravel IDs.
- Decide foreign-key deletion behavior for users, organizations, contacts, leads, tasks, notes, and activities.
- Decide which fields require indexes for search/filter performance.
- Decide whether full-text search is needed in MySQL for CRM records or whether basic LIKE search is enough for v1.
- Decide whether pipeline/stage history needs its own table.
- Decide whether assignments, tags, and watchers use pivot tables.
- Decide whether note/activity content supports plain text only or markdown.
- Decide whether timestamps need timezone-specific display but UTC storage.
- Decide migration rollback expectations during active development.

## 14. Notifications And Email

- Decide mailer/provider for production.
- Decide notification channels for internal lead alerts, task reminders, follow-up digests, failed jobs, and security events.
- Decide whether notifications are database-backed so they can appear in the app.
- Decide email templates and branding for visitor confirmations, staff invites, password resets, and lead alerts.
- Decide whether notification preferences are available per user in v1.
- Decide whether reminders should be sent only during business hours.
- Decide whether bounces/retries need operational visibility.

## 15. Testing And Quality Gates

- Decide required checks before merge: `composer test`, `composer test:types`, `composer test:lint`, `npm run build`, targeted Pest suites, and optional browser smoke tests.
- Decide whether browser tests are required for critical auth, marketing, CRM, and admin screens.
- Decide whether architecture tests enforce action/controller/model conventions.
- Decide minimum feature-test coverage for permissions and unauthorized access.
- Decide whether factories and seeders must exist before UI integration begins.
- Decide whether CI runs against MySQL, SQLite, Redis, or a matrix.
- Decide how to handle tests requiring queues, Horizon, mail, and notifications.
- Decide whether visual regression testing is out of scope or deferred.

## 16. Deployment, Environments, And Operations

- Decide target hosting environment and constraints for PHP 8.5, Node, MySQL, Redis, workers, scheduler, and Horizon.
- Decide environment names: local, testing, staging, production.
- Decide how `.env.example` represents database, Redis, queues, mail, app URL, and feature flags.
- Decide whether production deploys run migrations automatically.
- Decide backup strategy for database and uploaded files.
- Decide log aggregation and error tracking approach.
- Decide whether maintenance mode and rollback runbooks are needed before launch.
- Decide whether asset builds are performed in CI, on server, or in a deployment pipeline.
- Decide whether queue workers and scheduler are supervised by platform features, systemd, supervisor, or containers.

## 17. Security And Privacy

- Decide whether any stored lead/contact data is sensitive enough for special retention or redaction rules.
- Decide whether phone numbers, notes, budget, and project details need stricter audit treatment.
- Decide rate limits for login, registration, password reset, contact form, and API-like endpoints.
- Decide whether admin actions require recent password confirmation.
- Decide whether session lifetime differs for admins and regular users.
- Decide whether content security policy, secure cookies, trusted proxies, and HTTPS enforcement are part of v1 hardening.
- Decide whether file uploads are allowed, scanned, and access-controlled.
- Decide whether privacy policy and terms pages are required on the marketing site.

## 18. Documentation And Runbooks

- Decide which documentation must be updated alongside implementation: README, roadmap, runbooks, environment setup, deployment, queues, permissions, and testing.
- Decide whether decision records should be captured as ADRs in `docs/adr`.
- Decide whether product decisions should be tracked in the roadmap or a separate product spec.
- Decide whether admin/user documentation is needed for CRM and role management.
- Decide whether each phase requires a short completion note with shipped features, migrations, and verification commands.
