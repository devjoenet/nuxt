# Application Development Decision Points

This document tracks unresolved decisions that can materially change scope, architecture, launch criteria, or operating
behavior for the Southeast Code app.

The task documents and `docs/roadmap.md` already define an executable default path. If a decision below is not completed
before implementation reaches it, follow the default path in the roadmap and record the tradeoff. Do not reopen broad
scope questions in feature work unless a decision here explicitly changes the baseline.

## Completion Standard

A decision is complete when it records:

- the selected option;
- the reason that option was chosen;
- the affected docs, routes, models, tests, or deployment settings;
- the acceptance criteria that prove the project can continue without stopping on that question again.

## Resolved Baseline, Not Open Decisions

These items are already resolved by the roadmap unless this document is updated:

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

## Open Decisions

| ID    | Decision                                                                | Why It Matters                                                                                                                      | Required Resolution                                                                                                                                        | Default If Unanswered                                                                                                                                              |
|:------|:------------------------------------------------------------------------|:------------------------------------------------------------------------------------------------------------------------------------|:-----------------------------------------------------------------------------------------------------------------------------------------------------------|:-------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| DP-01 | What is the v1 success metric and launch gate?                          | Without this, "done" can drift between lead capture, internal visibility, operations automation, and starter-kit reuse.             | Choose one primary success metric, any secondary metrics, and the minimum workflows required for launch.                                                   | Launch requires public intake, CRM lead workflows, admin permissions, jobs access, audit visibility, and passing verification commands.                            |
| DP-02 | What is the final registration and account lifecycle?                   | Fortify registration exists, but an internal operations app usually wants invite-first staff access.                                | Decide whether public registration remains, is disabled after invites, or creates a restricted client/user role. Define deactivation/session behavior.     | Move to invite-first staff access after admin user management exists; no default role for public self-registration.                                                |
| DP-03 | Do clients log in during v1?                                            | Client users affect roles, policies, navigation, onboarding, privacy, and testing.                                                  | Confirm whether `Client Viewer` is an active v1 role or a reserved later role. Define what a client can see if active.                                     | Client portal and client login are deferred; `Client Viewer` may be seeded but receives no active portal surface.                                                  |
| DP-04 | Are there CRM model exceptions to the default v1 model?                 | Separate opportunity/project records, individual clients, and conversion output change migrations, actions, policies, and UI.       | Decide whether to add a separate `Opportunity` model, whether contacts can become clients without organizations, and whether conversion creates a project. | Use one `Lead` pipeline model; allow individual contacts; conversion marks organization/contact as client without creating projects.                               |
| DP-05 | Should pipeline/status values remain code-owned or become configurable? | Configurable stages require settings UI, validation, ordering, audit logging, and migration strategy.                               | Choose PHP enums only, database-backed stages/statuses, or a hybrid. Define who can edit them and how history is preserved.                                | Use PHP enums for v1; revisit admin-editable stages after the CRM proves useful.                                                                                   |
| DP-06 | What CRM ownership and visibility rules apply to sales/ops users?       | Ownership affects every CRM query, policy, dashboard metric, assignment action, and unauthorized-access test.                       | Decide whether sales/ops users can see all CRM records, only owned records, or owned plus assigned/team records. Define transfer rules.                    | Admins see all; sales/ops see owned/assigned records; super admins bypass through policy/Gate behavior.                                                            |
| DP-07 | What exactly does the public intake form collect?                       | Fields affect validation, database columns, privacy copy, spam controls, notification content, and lead quality.                    | Choose required/optional fields, consent copy, attachment support, attribution fields, and spam controls.                                                  | Require name, email, project details, and consent; optional company, phone, website, budget, timeline, service interest, referral source; no attachments.          |
| DP-08 | Who gets notified, through which channels, and when?                    | Notifications affect queues, mail setup, failed-job handling, business expectations, and tests.                                     | Define internal recipients, visitor confirmation behavior, scheduling-link behavior, reminder cadence, and channels for failed jobs/long waits.            | Internal email/database lead alert is required; visitor confirmation and scheduling link are optional; failed-job/long-wait recipients wait for deployment choice. |
| DP-09 | What audit retention and redaction rules are required?                  | Audit choices affect storage growth, searchable fields, privacy risk, and what admins may inspect or delete.                        | Define logged models/events, redacted fields, retention period, searchable filters, and whether audit entries can be deleted.                              | Log CRM/admin/settings/security-sensitive events; redact secrets/auth data; retain indefinitely until legal/privacy requirements say otherwise.                    |
| DP-10 | What is the deployment target and production operations model?          | Hosting determines PHP/Node availability, Redis, workers, scheduler, backups, logs, and asset build strategy.                       | Choose hosting, environment names, migration strategy, worker/scheduler supervision, Redis availability, backup policy, and error tracking.                | Assume staging/production support PHP 8.5, Node builds, MySQL, Redis, Horizon, scheduler, workers, backups, and centralized logs.                                  |
| DP-11 | What frontend component standard should v1 follow?                      | Mixed component systems can create duplicate form/table/dialog patterns and inconsistent accessibility.                             | Decide Nuxt UI first, local primitives first, or a strict hybrid. Define table, drawer/modal, toast, confirmation, and mobile behavior.                    | Use Nuxt UI where it fits; create local wrappers for repeated CRM/admin form, table, dialog, and status patterns.                                                  |
| DP-12 | Are privacy/legal pages required before public lead capture launches?   | Public intake may collect personal and business information, which can require privacy terms, retention copy, and consent language. | Decide required privacy policy, terms, consent wording, retention statements, and whether marketing footer links are mandatory.                            | Add privacy policy and basic contact-data consent before launch if public intake is enabled.                                                                       |

## Decision Hygiene

- Do not add a new decision if an existing decision can be amended.
- Do not use this file for implementation tasks; add those to the relevant phase document.
- Do not ask "should we do this eventually?" unless the answer changes v1 scope, architecture, launch, or operations.
- When a decision is completed, update the affected phase docs and roadmap in the same change.
