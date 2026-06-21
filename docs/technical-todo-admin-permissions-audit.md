# Technical To-Do: Phase 4 — Admin, Permissions, And Audit

## Goals

Wire first-class role/permission management, protect operational areas, and provide visibility into sensitive changes.

## Spatie Permission Integration

- Add Spatie's `HasRoles` trait to the `User` model.
- Add permission constants or enum-like classes for all capability names.
- Create a roles and permissions seeder for `Super Admin`, `Admin`, `Sales/Ops`, and `Client Viewer`.
- Add a super-admin bypass using Gate/Spatie best practices if selected.
- Assign default roles in seeders or invite flow.
- Decide and implement whether public registration assigns no role, a client role, or is disabled.

## Authorization Layer

- Add route middleware for CRM, admin, jobs, settings, and audit sections.
- Add or complete policies for users, roles, permissions, CRM entities, settings, jobs, and activity logs.
- Share authenticated user permissions/roles through Inertia middleware for navigation and UI controls.
- Hide navigation items and page actions based on permissions.
- Add tests proving unauthorized users cannot view pages or perform actions.

## Admin Dashboard

- Create admin dashboard controller/query action.
- Build cards for leads, open opportunities, follow-ups due, failed jobs, recent activity, and recent users.
- Add date-range support if selected.
- Add cached or query-object-backed metrics if dashboard queries become expensive.
- Build frontend dashboard components for metric cards, recent activity, and operational alerts.

## User Management

- Create admin user index/detail pages.
- Create invite-user flow with request validation and notification.
- Create actions for inviting users, deactivating users, reactivating users, assigning roles, resending verification, and resetting 2FA/recovery codes.
- Add user management policies and tests.
- Add UI for user status, verified status, 2FA status, roles, created date, and last update.

## Role And Permission Management

- Create role index/detail/edit pages.
- Create guarded role assignment and permission update flows.
- Prevent accidental editing/deletion of protected system roles if selected.
- Add tests for super-admin-only role management.
- Add activity logging for role and permission changes.

## Activity Logging And Audit UI

- Configure Spatie Activitylog for selected models.
- Add model-level logging configuration or observers for CRM, users, roles, permissions, settings, and sensitive operations.
- Redact excluded fields such as passwords, tokens, secrets, and sensitive contact details as decided.
- Create audit log index page with filters for causer, subject, event, model type, date range, and search.
- Create audit detail view showing safe before/after changes.
- Add tests for activity records on create/update/delete/role/settings operations.

## Business Settings

- Create settings storage approach for lead statuses, pipeline stages, notification recipients, company profile, and default task rules.
- Create request classes and actions for settings updates.
- Create settings UI with permission protection and audit logging.
- Add tests for validation, authorization, and activity logging.
