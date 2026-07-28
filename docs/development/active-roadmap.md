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
stable; numeric order is not a promise that findings were copied from the audit in their original order. The
2026-07-28 audit is consolidated as follows:

- F-10 is Task 29 and must land before the remaining findings so the roadmap, user-testing status, and deployment
  contracts are trustworthy.
- F-01, F-02, and F-03 are Task 30 because default role access, dashboard scoping, and related-record disclosure are one
  CRM visibility boundary.
- F-04 is Task 31; F-05 is Task 32.
- F-08 is Task 33; F-07 is Task 34; F-06 is Task 35; F-09 is Task 36.

__Note: Tasks that have been completed are moved to `docs/development/completed-roadmap.md`__

Detailed implementation instructions are in docs/development/active-tasks; this roadmap remains the ordered active-work index.

### Task 29 — Reconcile User-Testing And Deployment Documentation Contracts

[View Task 29 instructions](active-tasks/29-reconcile-user-testing-and-deployment-documentation-contracts.md)

### Task 30 — Harden CRM Role And Ownership Visibility

[View Task 30 instructions](active-tasks/30-harden-crm-role-and-ownership-visibility.md)

### Task 31 — End Deactivated Sessions At Fortify Boundaries

[View Task 31 instructions](active-tasks/31-end-deactivated-sessions-at-fortify-boundaries.md)

### Task 32 — Preserve User-Testing State And Rename Its Permission

[View Task 32 instructions](active-tasks/32-preserve-user-testing-state-and-rename-its-permission.md)

### Task 33 — Remove Dead Shared UI Actions

[View Task 33 instructions](active-tasks/33-remove-dead-shared-ui-actions.md)

### Task 34 — Optimize Public Landing Assets

[View Task 34 instructions](active-tasks/34-optimize-public-landing-assets.md)

### Task 35 — Centralize And Bound Service Interests

[View Task 35 instructions](active-tasks/35-centralize-and-bound-service-interests.md)

### Task 36 — Enforce Dependency Audit Policy

[View Task 36 instructions](active-tasks/36-enforce-dependency-audit-policy.md)

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
