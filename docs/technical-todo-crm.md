# Technical To-Do: Phase 3 — CRM MVP

## Goals

Replace external demo customer data with first-party CRM records and provide practical daily workflows for leads, contacts, organizations, follow-ups, and conversion.

## Database And Models

- Create migrations for organizations/accounts.
- Create migrations for contacts.
- Create migrations for leads/opportunities.
- Create migrations for notes.
- Create migrations for activities/timeline events.
- Create migrations for tasks/follow-ups.
- Create migrations for tags and taggables if tags are in v1.
- Create migrations for pipeline stages/statuses if configurable in database.
- Add foreign keys, indexes, soft deletes/archive fields, ownership fields, and UUIDs according to project conventions.
- Create Eloquent models with relationships, casts, scopes, and factories.
- Add PHP enums for statuses, stages, task priority, activity type, and lead source if code-owned.
- Add seeders for realistic organizations, contacts, leads, opportunities, activities, notes, and tasks.

## Backend Actions And Data Objects

- Create action classes for:
  - Creating a lead.
  - Updating a lead.
  - Archiving/restoring a lead.
  - Converting a lead/opportunity to a client/project.
  - Creating/updating contacts.
  - Creating/updating organizations.
  - Creating notes and activities.
  - Creating/completing follow-up tasks.
  - Assigning or transferring ownership.
- Create request classes for all create/update/archive/convert operations.
- Create resources or DTOs for list rows, detail pages, form options, filters, and dashboard summaries.
- Create query/filter classes for search, status, owner, due date, source, tag, sort, and pagination.
- Create policies for organizations, contacts, leads/opportunities, notes, activities, and tasks.

## Controllers And Routes

- Add CRM route group protected by authentication and CRM permissions.
- Add controllers for lead/opportunity list, detail, create, update, archive/delete, and conversion.
- Add controllers for organizations and contacts.
- Add nested or dedicated controllers for notes, activities, and tasks.
- Use named routes and Wayfinder so frontend links/forms do not hardcode URLs.
- Ensure server-side pagination, filtering, and sorting are consistently implemented.

## Frontend Pages And Components

- Replace `resources/js/pages/Customers.vue` demo API usage with first-party Inertia props/actions.
- Rename or split the customers page into CRM-specific pages after the route decision.
- Create CRM list pages with search, filters, sorting, pagination, and empty states.
- Create detail pages with summary header, contact details, pipeline/status, notes, activities, and follow-ups.
- Create create/edit forms using Inertia `<Form>` and Wayfinder.
- Create archive/delete confirmation flows.
- Create conversion UI for moving a lead/opportunity to client/project state.
- Create reusable CRM components for status badges, owner selectors, stage selectors, task rows, note forms, activity timeline items, and contact cards.
- Add permission-aware UI hiding for create, edit, archive, assignment, and conversion controls.

## Tests

- Add Pest feature tests for CRM CRUD.
- Add validation tests for each request class.
- Add authorization tests for each policy capability.
- Add tests for filters, search, sorting, and pagination.
- Add tests for lead conversion side effects.
- Add tests for task completion and overdue/due-soon scopes.
- Add factory tests where relationships are complex.
- Run build/type/lint checks after frontend integration.
