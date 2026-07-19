# Southeast Code Roadmap

This roadmap tracks the work needed to turn the current Laravel/Inertia starter kit into a functional Southeast Code
operating app: customer-facing marketing, a basic CRM, and an admin area for users, roles, permissions, queues, jobs,
audit trails, and business operations.

If at any time while adding a task, a decision with an unclear answer exists, it must be added here. 

## Implementation Contract

These defaults make the roadmap executable even when `docs/00-decision-points.md` has not been completed yet. If a
decision is later made differently, update this roadmap and the affected phase document before implementation continues.

- Build the Southeast Code internal operating app first. Starter-kit reuse is secondary and must not keep generic demo
  screens, generic copy, or placeholder APIs alive.
- Ship v1 as a single Southeast Code workspace. Multi-company tenancy and a client portal are later work.
- Use the CRM vocabulary `Lead -> Client` for v1. A `Lead` carries pipeline stages that can represent opportunity
  progress; do not add a separate `Opportunity` model unless the decision document is completed differently.
- Model `Organization` as the account/client record. `Contact` belongs to an organization when one is known, but an
  intake submission may create an individual contact without an organization.
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

The next tasks are ordered around the remaining unchecked work and the decisions that can change implementation scope. 

__Note: Tasks that have been completed are moved to `./completed-roadmap.md`__

### Task 17 — Refine Audit Log Filters

User testing found three audit-screen issues: the search placeholder overlaps the magnifying-glass icon, the Subject
model-or-ID field is too verbose/truncated, and Event, Causer, and Subject should offer constrained multi-select options.

- [ ] Fix the Search activity field spacing so its placeholder and icon never overlap in supported themes and widths.
- [ ] Shorten the Subject model-or-ID label/placeholder while keeping the filter's meaning clear.
- [ ] Replace the Event, Causer, and Subject free-text filters with accessible multi-select inputs populated only from
  valid available options.
- [ ] Preserve server-side filtering, authorization, and empty-state behavior; add focused coverage for the new filter
  combinations.

### Task 18 — Repair User Invitation And Verification Flows

User testing found that the invitation form sends a password-reset email without explaining the actual onboarding step,
the resend-confirmation action generates a link that returns 404, and the 2FA reset action shown for a user pending
email verification has no effect.

- [ ] Create a dedicated invitation/onboarding email that accurately explains the recipient's next step instead of
  presenting the flow as a generic password reset.
- [ ] Trace the resend-confirmation route and signed URL generation, then make the link resolve successfully from the
  delivered email; add regression coverage for the generated URL and endpoint.
- [ ] Make the pending-verification 2FA action either perform the intended password-confirmed reset or become clearly
  unavailable with explanatory UI; do not leave a button that silently does nothing.
- [ ] Add focused authorization, mail, signed-link, and state-transition tests for invitation, email verification, and
  2FA reset behavior.

### Task 19 — Simplify Role Editing And Super Admin Presentation

User testing requested a role-first editor with one larger role selector and a dynamically loaded permission list, plus
an informational treatment for the non-editable Super Admin role.

- [ ] Replace the all-roles permission lists with a prominent accessible role selector and load the selected role's
  permissions into one focused list without weakening server-side authorization.
- [ ] Render the Super Admin permissions area as a compact info banner stating that all permissions are granted by the
  server-side Gate bypass and that the role is not editable from the UI, with an info icon on the left.
- [ ] Preserve guarded role/permission behavior, loading, empty, and error states; add focused coverage for role
  selection, permission updates, and Super Admin non-editability.

### Task 21 — Restore CRM Detail Browser Smoke Coverage

- [ ] Diagnose why Pest's temporary browser HTTP server blank-renders CRM lead, organization, and contact detail URLs
  while list pages and the other browser smoke flows render normally.
- [ ] Reinstate focused CRM browser coverage that separately verifies View link navigation and the linked detail-route
  response for leads, organizations, and contacts.
- [ ] Run the browser suite and full quality contract after the detail-route rendering issue is resolved.

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
