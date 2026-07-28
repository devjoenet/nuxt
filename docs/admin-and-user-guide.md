# Admin And User Guide

This guide describes the shipped Southeast Code v1 workspace. It is for staff who manage inbound opportunities and
for administrators who manage access and operational health. Client portal access, proposals, invoices, campaigns,
imports, and exports are not part of v1.

## Getting access

Staff accounts are invite-first. A user with `admin.users.manage` opens **Admin > Users**, enters the person's name,
email, and role, and sends the invitation. The recipient sets a password from the emailed reset link, verifies the
email address, and should enable two-factor authentication from **Settings > Security**.

Public registration is disabled. Deactivated users cannot enter the authenticated workspace; an administrator can
reactivate them from **Admin > Users**. Users cannot deactivate their own account.

## Roles and permissions

Permissions are capability-based and are enforced on the server. Navigation visibility is only a convenience; a hidden
link is not the access boundary.

| Role | Intended use | Access |
| --- | --- | --- |
| Super Admin | System owner and break-glass administrator | All seeded permissions; can manage protected role and Super Admin access |
| Admin | Day-to-day workspace administrator | Dashboard, users, roles, CRM, jobs, settings, and audit |
| Sales/Ops | CRM operator | Dashboard, CRM, and read-only jobs visibility; ownership rules still apply to CRM records |
| Client Viewer | Reserved future client access | No v1 application permissions; no active client portal is shipped |

The current permission set is `crm.view`, `crm.manage`, `admin.dashboard.view`, `admin.users.manage`,
`admin.roles.manage`, `jobs.view`, `jobs.manage`, `settings.manage`, and `audit.view`.

Only a Super Admin can assign the Super Admin role. The Super Admin role's permissions are managed in code and cannot
be edited from the roles screen. Do not remove the last usable Super Admin account.

## CRM lifecycle

1. A visitor submits the public contact form. The submission creates a first-party lead and sends an internal alert to
   the business notification email on the mail queue.
2. A CRM operator opens **CRM > Leads**, reviews the source and status, and assigns an owner when appropriate. Sales/Ops
   users see records they own or records that are unassigned; Admin and Super Admin users can see all records.
3. The operator creates or updates the organization and contact, adds notes, records activities, and creates follow-up
   tasks. Notes, activities, and tasks must remain attached to an authorized CRM record.
4. The operator advances the lead through the code-owned status/stage values and completes follow-up tasks as work is
   done.
5. When the lead is ready, **Convert to client** marks the organization as a client, records the conversion activity,
   and avoids creating a duplicate client record. Conversion requires an organization; individual contacts remain valid
   without one until conversion is appropriate.
6. Archive records when they should leave normal worklists. Restore them from the record workflow when work resumes.
   Destructive actions and ownership transfers are permission-protected and activity-logged.

Out-of-scope CRM records return `404` on direct URLs to avoid revealing their existence. Visible records with an
unauthorized mutation return `403`.

## User administration

From **Admin > Users**, administrators can:

- invite a user and assign a seeded role;
- activate or deactivate access;
- resend email verification;
- reset a user's two-factor authentication; and
- change a user's assigned role subject to Super Admin protections.

Every invitation, access change, verification resend, and two-factor reset creates an audit entry. Use the smallest
role that supports the person's job. Prefer deactivation over deleting a user so historical audit entries retain their
causer context.

## Roles, settings, and permissions

From **Admin > Roles**, users with `admin.roles.manage` can update permissions for non-protected roles. The role change
itself is logged. From **Admin > Settings**, users with `settings.manage` can update the business profile, notification
email, and task defaults. Internal lead alerts and follow-up reminders use the current business notification email;
visitor confirmation and scheduling links remain deferred.

## Jobs and Horizon

Open **Admin > Jobs & queues** to see the configured queue connection, pending jobs, failed jobs, batches, and links to
Horizon. Users need `jobs.view` to inspect operations. Retrying or otherwise mutating queue state requires
`jobs.manage`.

For a failed job:

1. Inspect the failure and timestamps without copying secrets, contact data, or raw payloads into tickets.
2. Confirm the underlying database, Redis, mail, or application issue is fixed.
3. Retry only the affected job from Horizon with `jobs.manage` access.
4. Record recurring failures as an application or configuration issue rather than repeatedly retrying them.

Horizon metrics depend on the scheduler's five-minute snapshot. Follow [queues-and-horizon.md](queues-and-horizon.md)
for worker timeouts, retry windows, long-wait alerts, and deployment restarts.

## Audit review

Open **Admin > Audit** with `audit.view` to search safe descriptions, event names, log names, causers, subjects, and
date ranges. Review audit entries after role changes, access changes, settings changes, CRM ownership changes, queue
mutations, and failed-job handling.

The audit log intentionally excludes passwords, tokens, recovery codes, two-factor secrets, note bodies, contact email
and phone values, notification recipients, and exception messages. Entries are retained for 365 days and cleaned daily
at 02:15. Audit logs are review-only in v1; do not delete entries manually.

## Escalation

The Southeast Code Operations Lead owns access reviews, incident coordination, and backup/restore readiness. Escalate
application, migration, release, queue, or infrastructure failures to the Technical Maintainer. Use the monitored
`HORIZON_NOTIFICATION_EMAIL` mailbox for operational alerts and follow [deployment-and-backups.md](deployment-and-backups.md)
for Forge release and restore procedures.
