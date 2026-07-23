# Southeast Code Roadmap

This roadmap tracks the work needed to turn the current Laravel/Inertia starter kit into a functional Southeast Code
operating app: customer-facing marketing, a basic CRM, and an admin area for users, roles, permissions, queues, jobs,
audit trails, and business operations.

If at any time while adding a task, a decision with an unclear answer exists, it must be added here.

## Implementation Contract

These defaults make the roadmap executable even when `docs/development/active-decision-points.md` has not been completed yet. If a
decision is later made differently, update this roadmap and the affected phase document before implementation continues.

- Build the Southeast Code internal operating app first. Starter-kit reuse is secondary and must not keep generic demo
  screens, generic copy, or placeholder APIs alive.
- Ship v1 as a single Southeast Code workspace. Multi-company tenancy and a client portal are later work.
- Use the CRM vocabulary `Lead -> Client` for v1. A `Lead` carries pipeline stages that can represent opportunity
  progress; do not add a separate `Opportunity` model unless the decision document is completed differently.
- Model `Organization` as the account/client record. `Contact` belongs to an organization when one is known, but an
  intake submission may create an individual contact without an organization.
- Treat public intake as untrusted identity data. Every submission creates a new unowned Contact and Lead, never
  reuses or mutates an existing CRM Contact or Organization, remains unlinked from an Organization until staff resolve
  it, and preserves the submitted identity and company fields in an immutable Lead snapshot.
- Preserve Lead history when a related Contact is soft-deleted. Load that Contact with trashed records, expose its
  deleted state, and render the historical identity without linking to a route that cannot resolve the deleted record.
- Keep pipeline/status values code-owned with PHP enums for v1. Admin-editable pipeline configuration is later unless
  explicitly pulled into scope.
- Use server-side Laravel authorization as the source of truth. Inertia may receive permission flags for navigation and
  button visibility, but hidden UI is never the authorization boundary.
- Use named routes and Wayfinder-generated imports for frontend links and forms. Do not hardcode application URLs in Vue
  pages.
- Use Pest feature tests for every shipped workflow and policy/authorization boundary. Add focused unit tests only where
  domain logic is isolated enough to make them useful.
- Route state-changing workflows through single-purpose action classes. Controllers, jobs, commands, tests, and Tinker
  must call the same action; browser automation is for UI behavior and must not be the backend fixture mechanism.
- Keep transport validation in Form Requests while giving actions an explicit raw-input validation path whenever they
  need to be callable outside HTTP.
- Use database queues for local/simple development defaults and Redis plus Horizon for staging/production operations.
  Tests should fake queues/notifications unless a persistence or Horizon authorization behavior is under test.
- Use the business notification email for internal lead/reminder mail, keep visitor confirmations and scheduling links
  deferred, and retain audit records for 365 days with scheduled cleanup.
- Deploy staging and production as separate Laravel Forge-managed sites with PHP 8.5, MySQL/Redis, Supervisor-managed
  Horizon workers, Forge scheduler jobs, daily encrypted backups with isolated restore tests, Forge logs, `/up`
  monitoring, and named Operations Lead/Technical Maintainer ownership.

## Active Development Tasks

The active tasks are ordered by urgency first and implementation effort second. Existing task identifiers remain
stable; numeric order is not a promise that findings were copied from the audit in their original order. The audit
work is consolidated as follows:

- F-02 is part of Task 21 because its restore controls and browser coverage already live in the organization/contact
  detail workspace change.
- F-04 is Task 22; F-03 is Task 23.
- F-01 and F-11 are Task 24 because safe UUID-based identity selection and bounded searchable option endpoints are one
  boundary.
- F-05 and the CRM portion of F-06 are Task 25.
- F-08, the audit portion of F-06, and `ARCH-QUERY-001` are Task 26.
- F-07 is Task 27, and F-09 is Task 28.
- F-10 is already reconciled and does not need a duplicate implementation task.

__Note: Tasks that have been completed are moved to `docs/development/completed-roadmap.md`__

Detailed implementation instructions are in docs/development/active-tasks; this roadmap remains the ordered active-work index.

### Task 22 — Preserve Lead History When A Contact Is Deleted

[View Task 22 instructions](active-tasks/22-preserve-lead-history-when-a-contact-is-deleted.md)

### Task 23 — Mark Follow-Up Reminders Sent Only After Delivery

[View Task 23 instructions](active-tasks/23-mark-follow-up-reminders-sent-only-after-delivery.md)

### Task 24 — Isolate Intake Identity And Bound CRM Record Selectors

[View Task 24 instructions](active-tasks/24-isolate-intake-identity-and-bound-crm-record-selectors.md)

### Task 25 — Type CRM Filters And Scope Related Counts

[View Task 25 instructions](active-tasks/25-type-crm-filters-and-scope-related-counts.md)

### Task 26 — Type And Scale The Audit Query Boundary

[View Task 26 instructions](active-tasks/26-type-and-scale-the-audit-query-boundary.md)

### Task 27 — Move Coherent State Changes Into Actions

[View Task 27 instructions](active-tasks/27-move-coherent-state-changes-into-actions.md)

### Task 28 — Enable Strict Frontend Lint Guardrails

[View Task 28 instructions](active-tasks/28-enable-strict-frontend-lint-guardrails.md)

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
