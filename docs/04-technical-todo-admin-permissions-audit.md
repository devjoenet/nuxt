# Technical To-Do: Phase 4 — Admin, Permissions, And Audit

## Goals

Wire first-class role/permission management, protect operational areas, and provide visibility into sensitive changes.

## Execution Baseline

- Initial roles are `Super Admin`, `Admin`, `Sales/Ops`, and `Client Viewer`.
- Seed permissions around capabilities, not pages:
  - `crm.view`
  - `crm.manage`
  - `admin.dashboard.view`
  - `admin.users.manage`
  - `admin.roles.manage`
  - `jobs.view`
  - `jobs.manage`
  - `settings.manage`
  - `audit.view`
- `Super Admin` may bypass policy checks through the selected Gate/Spatie before hook pattern.
- Server-side policies and middleware are mandatory. Inertia role/permission props only drive navigation and action
  visibility.
- Move toward invite-first staff access. Public registration may remain only until the invite flow is functional; after
  that, keep public registration disabled unless DP-02 resolves otherwise.
- Activity logging must never store passwords, tokens, recovery codes, secrets, raw 2FA material, or other sensitive
  authentication payloads.

## Spatie Permission Integration

- Add Spatie's `HasRoles` trait to the `User` model.
- Add permission constants or enum-like classes for all capability names.
- Create a roles and permissions seeder for `Super Admin`, `Admin`, `Sales/Ops`, and `Client Viewer`.
- Add a super-admin bypass using Gate/Spatie best practices.
- Assign default roles in seeders and the invite flow.
- Disable public staff registration after invite-based admin management exists unless DP-02 resolves otherwise.

## Authorization Layer

- Add route middleware for CRM, admin, jobs, settings, and audit sections.
- Add or complete policies for users, roles, permissions, CRM entities, settings, jobs, and activity logs.
- Share authenticated user permissions/roles through Inertia middleware for navigation and UI controls.
- Hide navigation items and page actions based on permissions.
- Add tests proving unauthorized users cannot view pages or perform actions.

## Admin Dashboard

- Create admin dashboard controller/query action.
- Build cards for leads, qualified pipeline leads, follow-ups due, failed jobs, recent activity, and recent users.
- Add date-range support for dashboard metrics.
- Add cached or query-object-backed metrics if dashboard queries become expensive.
- Build frontend dashboard components for metric cards, recent activity, and operational alerts.

## User Management

- Create admin user index/detail pages.
- Create invite-user flow with request validation and notification.
- Create actions for inviting users, deactivating users, reactivating users, assigning roles, resending verification,
  and resetting 2FA/recovery codes.
- Add user management policies and tests.
- Add UI for user status, verified status, 2FA status, roles, created date, and last update.

## Role And Permission Management

- Create role index/detail/edit pages.
- Create guarded role assignment and permission update flows.
- Prevent accidental editing/deletion of protected system roles.
- Add tests for super-admin-only role management.
- Add activity logging for role and permission changes.

## Activity Logging And Audit UI

- Configure Spatie Activitylog for selected models.
- Add model-level logging configuration or observers for CRM, users, roles, permissions, settings, and sensitive
  operations.
- Redact excluded fields such as passwords, tokens, secrets, raw 2FA material, recovery codes, note bodies, contact
  email/phone values, notification recipients, and exception messages.
- Create audit log index page with filters for causer, subject/model type, event, date range, and search across safe
  description, log/event, causer, and subject metadata.
- Create audit detail view showing safe before/after changes.
- Add tests for activity records on create/update/delete/role/settings operations.

## Business Settings

- Create settings storage for notification recipients, company profile, and default task rules.
- Keep lead statuses and pipeline stages code-owned/read-only in v1 unless DP-05 changes that default.
- Create request classes and actions for settings updates.
- Create settings UI with permission protection and audit logging.
- Add tests for validation, authorization, and activity logging.

## Acceptance Criteria

- `User` supports Spatie roles/permissions and seeded system roles are reproducible.
- A seeded super admin can access admin, jobs, settings, and audit areas; users without matching permissions cannot.
- CRM, admin, jobs, settings, and audit routes are protected by middleware and/or policies, not just hidden navigation.
- Inertia shared props expose only the role/permission information needed for navigation and action visibility.
- Invite, deactivate/reactivate, role assignment, verification resend, and 2FA reset flows are policy-protected and
  activity-logged.
- Protected system roles cannot be accidentally deleted or stripped of critical permissions.
- Activity log list/detail pages support filtering by causer, subject, event, model type, date range, and search.
- Activity entries redact excluded fields and show safe before/after values for auditable changes.
- Business settings updates are validated, permission-protected, and activity-logged.

## Verification

- User-run verification after this phase:
  - `composer test`
  - `composer test:types`
  - `composer test:lint`
  - `npm run build`
