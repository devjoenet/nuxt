---
name: crm-development
description: Integrate CRM organizations, contacts, leads, intake creation, follow-ups, reminders, ownership, history and shared UI in a modular Laravel application.
license: MIT
metadata:
  author: Joey Harris
---

# CRM integration

## Primary goal

Use the installed package's public API without host-private dependencies or duplicate schema ownership.

## Workflow

- For installation, schema ownership, middleware, optional disabling, or cache setup, read [installation](references/installation.md).
- For reminder scheduling, recipient configuration, retries, or delivery guarantees, read [reminders](references/reminders.md).
- Import models from Organizations, Contacts, Leads, FollowUps or Shared slices. Use the configured user's workspace identity methods and explicit actors. Keep CRM activity rows separate from Spatie audit history.
- Query Shared\Queries\CrmOwnerOptions for active assignees. Only append the record's existing inactive owner; label it inactive. Use assignmentRule() for new owners, or assignmentRule($record->owner_id) to retain an existing owner. Never trust the submitted id as the existing owner. Keep request-specific authorization/rules.
- Normalize declared filters with Shared\Http\CrmFilterInput::normalize; retain invalid values for validation and keep allowed keys explicit.
- Import components/types/helpers from @southeastcode/crm/public and transport from @southeastcode/transport/crm/options, /organizations, /contacts or /leads. Pages use crm::Organizations/*, crm::Contacts/* and crm::Leads/*; keep existing named routes and UUID binding. The host must expose the supported public UI API, including FormTextarea and Stat. Opt into the public CrmWorkspaceDashboard component in the host dashboard. Run module preparation, types, lint, client/SSR and browser checks after changes.
- Organization/contact routes and navigation are package-owned. Their history requires record access plus audit.view; domain Audit presenters register automatically. Do not duplicate host routes/presenters. Pass an explicit configured Eloquent workspace actor to Actions, including direct calls.
- Keep public intake validation, anti-abuse rules, host enums, recipient selection and notifications in the host. Map validated input to Leads\Data\IntakeLeadData with an explicit consentedAt, then call Leads\Actions\CreateLeadFromIntake. Wrap host creation/notification orchestration in a transaction and mark queued notifications afterCommit(). Do not fabricate consent for staff-created leads. Configure crm.service_interests as a list of string label/value pairs for staff forms.

## References

- README.md for installation and public contracts
- config/crm.php and routes/crm.php for middleware and selectors
- database/migrations for sole CRM schema ownership

## Example

Use CrmOwnerOptions::get($record->owner_id) for edit props and the public ownerOptions helper for marked inactive labels. New forms call get() without a historical owner. Filters may allow historical selection without permitting new assignment.

## Anti-patterns

- No App namespace imports, user-management dependency, operational data imports, or host-private frontend imports.
- Do not fabricate staff consent or run obsolete correction/role/gallery migrations.
- Do not add duplicate owner queries or filter normalization when extending CRM CRUD.
- Do not import old serialized jobs, move agency notifications into CRM, register duplicate reminder schedules, or claim exactly-once mail without provider delivery keys and reconciliation.
