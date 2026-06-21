# Technical To-Do: Phase 6 — Quality, Launch, And Later Modules

## Goals

Harden the application for daily use, prove the critical flows, and prepare deferred modules without prematurely building them.

## Test Coverage

- Ensure `composer test` passes with all feature and unit tests.
- Ensure `composer test:types` passes after all PHP domain additions.
- Ensure `composer test:lint` passes before merge.
- Ensure `npm run build` passes after all frontend work.
- Add browser smoke tests for public marketing, login, dashboard, CRM list/detail, admin users, roles, jobs, settings, and audit pages when practical.
- Add regression tests for unauthorized direct URL access.
- Add tests for queue dispatch, notification dispatch, and activity logging.

## UX Polish

- Replace all empty/demo states with Southeast Code-specific operational empty states.
- Review loading, error, validation, success, and destructive-confirmation states across CRM/admin/jobs/audit.
- Verify responsive behavior for marketing pages and authenticated workspaces.
- Ensure dense tables remain usable on smaller screens through responsive columns, drawers, or detail pages.
- Review keyboard navigation and focus states.
- Verify icons are consistently sourced from Lucide-Vue where custom icons are needed.
- Verify shared components are extracted rather than duplicated across CRM and admin.

## Security And Privacy Hardening

- Review auth, registration, password reset, email verification, and 2FA behavior.
- Confirm route and policy coverage for every authenticated feature.
- Confirm contact form rate limits and spam controls.
- Confirm sensitive fields are not logged in activity logs.
- Confirm file uploads are disabled or secured if introduced.
- Confirm production cookies, trusted proxies, HTTPS, and session settings.
- Add privacy/terms pages if required by the public lead-capture flow.

## Data And Operational Readiness

- Verify migrations run cleanly on a fresh database.
- Verify seeders produce useful local/demo data without polluting production system data.
- Verify backups and restore expectations are documented.
- Verify queue workers, scheduler, and Horizon are documented for deployment.
- Verify failed-job alerting and operational visibility.
- Verify admin users and initial roles can be bootstrapped safely.

## Documentation

- Update README with current setup, architecture, commands, and deployment notes.
- Keep `docs/roadmap.md` current as phases are completed.
- Add ADRs for major decisions if selected.
- Add admin/user documentation for CRM lifecycle, user management, roles, permissions, jobs, and audit logs.
- Add runbooks for queues, failed jobs, Horizon, backups, and common deployment issues.

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
