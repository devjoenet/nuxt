# Technical To-Do: Phase 6 — Quality, Launch, And Later Modules

## Goals

Harden the application for daily use, prove the critical flows, and prepare deferred modules without prematurely building them.

## Launch Gate

The app is ready for v1 launch only when these conditions are true:

- Public marketing and lead intake work without authentication.
- Authenticated CRM workflows cover lead list/detail, create/edit/archive/restore, notes, activities, tasks, ownership,
  and lead-to-client conversion.
- Admin workflows cover users, roles, permissions, settings, jobs access, and audit review.
- All authenticated feature routes have policy or middleware coverage and unauthorized direct URL access is tested.
- No visible demo data, external demo APIs, placeholder GitHub links, stale starter-kit copy, or generic Nuxt dashboard
  positioning remains in production-facing UI.
- Queue, scheduler, Horizon, failed-job, notification, backup, error-tracking, and ownership expectations are documented
  for the selected Laravel Forge deployment target.
- Critical data handling choices from `docs/00-decision-points.md` that affect launch are resolved or explicitly
  deferred in the roadmap.

## Test Coverage

- [x] Ensure `composer test` passes with all feature and unit tests.
- [x] Ensure `composer test:types` passes after all PHP domain additions.
- [x] Ensure `composer test:lint` passes before merge.
- [x] Ensure `npm run build` passes after all frontend work.
- [x] Add browser smoke tests for public marketing, login, dashboard, CRM lists, admin users, roles, jobs, settings,
  audit pages, and expressive form controls.
- [ ] Restore separate CRM detail browser smoke coverage. Pest's temporary HTTP server currently blank-renders CRM
  lead/entity detail URLs; this is deferred to Roadmap Task 21.
- [x] Add regression tests for unauthorized direct URL access.
- [x] Add tests for queue dispatch, notification dispatch, and activity logging.

Browser smoke coverage uses Pest's browser plugin with Playwright Chromium. The Browser PHPUnit suite runs through
`composer test:browser` and is included in `composer test`; the CI workflow installs the Chromium runtime before
running the quality contract.

## UX Polish

- [x] Replace all empty/demo states with Southeast Code-specific operational empty states.
- [x] Review loading, error, validation, success, and destructive-confirmation states across CRM/admin/jobs/audit.
- [x] Verify responsive behavior for marketing pages and authenticated workspaces.
- [x] Ensure dense tables remain usable on smaller screens through responsive columns, drawers, or detail pages.
- [x] Review keyboard navigation and focus states.
- [x] Verify icons are consistently sourced from Lucide-Vue where custom icons are needed.
- [x] Verify shared components are extracted rather than duplicated across CRM and admin.

## Security And Privacy Hardening

- [x] Review auth, registration, password reset, email verification, and 2FA behavior.
- [x] Confirm route and policy coverage for every authenticated feature.
- [x] Confirm contact form rate limits and spam controls.
- [x] Confirm sensitive fields are not logged in activity logs.
- [x] Confirm file uploads are disabled or secured if introduced.
- [x] Confirm production cookies, trusted proxies, HTTPS, and session settings.
- [x] Add a privacy policy and contact-data consent for public intake. Add terms only if DP-12 requires them.

## Data And Operational Readiness

- [x] Verify migrations run cleanly on a fresh database.
- [x] Verify seeders produce useful local/demo data without polluting production system data.
- [x] Verify backups and restore expectations are documented.
- [x] Verify queue workers, scheduler, and Horizon are documented for deployment.
- [x] Verify failed-job alerting and operational visibility.
- [x] Verify admin users and initial roles can be bootstrapped safely.

## Documentation

- [x] Update README with current setup, architecture, commands, and deployment notes.
- [x] Keep `docs/roadmap.md` current as phases are completed.
- [x] Add ADRs for completed decision points that change the roadmap defaults.
- [x] Add admin/user documentation for CRM lifecycle, user management, roles, permissions, jobs, and audit logs.
- [x] Add runbooks for queues, failed jobs, Horizon, backups, and common deployment issues.

## Later Modules To Revisit

- Client portal for project status, shared files, notes, and approvals.
- Proposal workflow with templates and approval states.
- Statement-of-work and contract workflows.
- Invoice and payment/accounting integrations.
- Advanced reporting dashboards and saved report views.
- Email campaign/newsletter tooling.
- Knowledge base or internal SOP section.
- Public case studies and portfolio management.
- Import/export pipelines for CRM data.

## Acceptance Criteria

- A fresh database can be migrated and seeded into a useful local state without production-only data.
- Required user roles can be bootstrapped safely, and at least one super admin path is documented.
- The public intake, CRM CRUD/conversion, admin users/roles, jobs access, settings update, and audit-log review flows
  are
  covered by focused Pest tests.
- Browser smoke coverage exists for critical auth, marketing, CRM, admin, jobs, settings, and audit pages when
  practical;
  otherwise the roadmap records why it is deferred.
- Responsive behavior is verified for marketing, authenticated navigation, dense CRM/admin tables, and forms.
- Security hardening is reviewed for registration, email verification, 2FA, session/cookie settings, trusted proxies,
  HTTPS, contact-form rate limits, and activity-log redaction.
- README, roadmap, deployment notes, queue/Horizon runbook, ADRs, and admin/user docs match the shipped behavior.
- Later modules remain documented as later work and do not leak half-built navigation or broken routes into v1.

## Verification

- User-run verification before launch:
  - `composer test`
  - `composer test:types`
  - `composer test:lint`
  - `npm run build`
  - targeted Pest filters for public intake, CRM permissions, admin permissions, jobs/Horizon access, settings, and
    audit
