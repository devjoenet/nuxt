# Technical To-Do: Phase 3 — CRM MVP

## Goals

Replace external demo customer data with first-party CRM records and provide practical daily workflows for leads,
contacts, organizations, follow-ups, and conversion.

## Scope Boundary

Phase 3 owns the authenticated CRM workspace. It starts from the Phase 2 lead-capture records and expands them into
daily
operations: list/detail screens, create/edit/archive flows, ownership, search/filtering, notes, activities, tasks, and
lead-to-client conversion. It does not build proposals, contracts, invoices, imports/exports, client portal access, or
admin-editable pipeline configuration.

## CRM Model Contract

- `Organization` is the account/client container. It may become a client through conversion by setting client-related
  state such as `client_since_at`.
- `Contact` represents a person and may belong to an organization. Individual contacts without an organization are
  valid.
- `Lead` is the pipeline entity for v1. It carries stage/status values that cover opportunity progress; do not create a
  separate `Opportunity` model in v1 unless DP-04 changes the model.
- `Note`, `Activity`, and `Task` should be polymorphic across CRM entities where practical, with explicit policies so
  users cannot attach records to unauthorized subjects.
- Use PHP enums for lead stage/status, task priority/status, activity type, and lead source unless DP-05 moves these to
  database-managed settings.
- Use soft deletes for user-created CRM records. Use an `archived_at` style state where users need archive/restore
  workflows distinct from deletion.
- Add indexes for owner, stage/status, due date, source, created date, organization/contact foreign keys, and fields
  used
  in search/filtering.
- Keep seeders split between system data and local/demo fixtures.

## Database And Models

- Create migrations for organizations/accounts.
- Create migrations for contacts.
- Create migrations for leads.
- Create migrations for notes.
- Create migrations for activities/timeline events.
- Create migrations for tasks/follow-ups.
- Defer tags/taggables until explicitly pulled into v1.
- Defer database-managed pipeline stages/statuses until explicitly pulled into v1.
- Add foreign keys, indexes, soft deletes/archive fields, ownership fields, and UUIDs according to project conventions.
- Task 8 adds indexed nullable `owner_id` fields to organizations and contacts and applies the same ownership contract
  to leads.
- Create Eloquent models with relationships, casts, scopes, and factories.
- Add PHP enums for statuses, stages, task priority, activity type, and lead source.
- Add seeders for realistic organizations, contacts, leads, activities, notes, and tasks.

## Backend Actions And Data Objects

- Create action classes for:
  - Creating a lead.
  - Updating a lead.
  - Archiving/restoring a lead.
  - Converting a lead to a client.
  - Creating/updating contacts.
  - Creating/updating organizations.
  - Creating notes and activities.
  - Creating/completing follow-up tasks.
  - Assigning or transferring ownership.
- Create request classes for all create/update/archive/convert operations.
- Create resources or DTOs for list rows, detail pages, form options, filters, and dashboard summaries.
- Create query/filter classes for search, status, owner, due date, source, sort, and pagination.
- Create policies for organizations, contacts, leads, notes, activities, and tasks.

## Controllers And Routes

- Add CRM route group protected by authentication and CRM permissions.
- Add controllers for lead list, detail, create, update, archive/delete, and conversion.
- Add controllers for organizations and contacts.
- Add nested or dedicated controllers for notes, activities, and tasks.
- Use named routes and Wayfinder so frontend links/forms do not hardcode URLs.
- Ensure server-side pagination, filtering, and sorting are consistently implemented.

## Frontend Pages And Components

- Replace `resources/js/pages/Customers.vue` demo API usage with first-party Inertia props/actions.
- Move or split the current customers page into CRM-specific pages under the `Crm/*` namespace.
- Create CRM list pages with search, filters, sorting, pagination, and empty states.
- Create detail pages with summary header, contact details, pipeline/status, notes, activities, and follow-ups.
- Create create/edit forms using Inertia `<Form>` and Wayfinder.
- Create archive/delete confirmation flows.
- Create conversion UI for moving a lead to client state.
- Create reusable CRM components for status badges, owner selectors, stage selectors, task rows, note forms, activity
  timeline items, and contact cards.
- Add permission-aware UI hiding for create, edit, archive, assignment, and conversion controls.
- Task 8 ships first-party `Crm/Organizations/*` and `Crm/Contacts/*` pages, with ownership-aware filters and admin-only
  assignment controls.

## Acceptance Criteria

- No CRM screen reads from the external Nuxt demo APIs.
- Authenticated users with `crm.view` can see CRM list/detail pages allowed by ownership policy; out-of-scope direct
  URLs return `404`.
- Users with management permissions can create, update, archive, restore, assign, and convert leads according to policy.
- Lead conversion updates the organization/client state, records an activity entry, completes or creates relevant
  follow-up tasks, and avoids duplicate client records.
- Search, filters, sorting, and pagination are server-side and preserve query state across Inertia visits.
- Notes, activities, and tasks are attached only to authorized CRM subjects.
- Empty, loading, validation-error, destructive-confirmation, and success states are consistent with the shared UI
  patterns from Phase 1.
- Factories and seeders can generate realistic linked organizations, contacts, leads, activities, notes, and tasks for
  local development and tests.

## Tests

- Add Pest feature tests for CRM CRUD.
- Add validation tests for each request class.
- Add authorization tests for each policy capability.
- Add tests for filters, search, sorting, and pagination.
- Add tests for lead conversion side effects.
- Add tests for task completion and overdue/due-soon scopes.
- Add factory tests where relationships are complex.
- User-run verification after frontend integration:
  - `composer test`
  - `composer test:types`
  - `composer test:lint`
  - `npm run build`

## Launch documentation

- [x] Document the Lead -> Client lifecycle, ownership visibility, archive/restore behavior, and deferred CRM modules
  in [the admin and user guide](admin-and-user-guide.md).
- [x] Record the ownership decision in [ADR 0003](adr/0003-crm-ownership-visibility.md).
