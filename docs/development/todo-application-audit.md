# Laravel Application Audit

**Audit date:** 2026-07-21  
**Document updated:** 2026-07-22  
**Mode:** Read-only, risk-based architecture and code-quality audit  
**Repository:** `/Users/joey/sites/nuxt`

## Executive summary

The application has a solid baseline: Laravel 13, PHP 8.5, typed Actions and DTOs in newer workflows, ownership scopes, policies, Pest feature tests, browser coverage, Larastan, Rector, Pint, ESLint, and TypeScript checking.

The highest-risk issues are:

1. Global identity upserts can mutate CRM records across ownership boundaries.
2. Organization and Contact restore controls use the wrong HTTP verb.
3. Follow-up reminders are marked sent before queued mail delivery succeeds.
4. Lead rendering can fail when its Contact is soft-deleted.

The most valuable next step is a focused CRM correctness batch covering restore transport, soft-deleted lead rendering, and visibility-scoped counts. The public-intake deduplication policy should be decided before changing F-01.

This audit was sampled rather than exhaustive. It did not include production traffic analysis, production mail delivery, Redis failure testing, MySQL execution plans, or high-volume load testing.

## Scope and baseline

Reviewed representative slices across:

- `app/Actions`, `app/Controllers`, `app/Models`, `app/Queries`, `app/Jobs`, `app/Notifications`, `app/Resources`, and policies.
- `resources/js` CRM, authentication, audit, and error-page surfaces.
- Relevant routes, migrations, configuration, deployment documentation, and tests.
- Composer/frontend manifests and quality-tool configuration.

Confirmed stack:

- PHP 8.5
- Laravel 13.21.1
- MySQL in the connected environment
- Inertia Laravel 3.1.1 and Inertia Vue 3.6.1
- Vue 3.5.40 and Nuxt UI 4.8.2
- Horizon 5.48.1
- Larastan 3.10.0, Pest 4.7.5, PHPUnit 12.5.30
- Rector 2.5.7, Pint 1.29.3
- ESLint 10.7.0, TypeScript 6.0.3, Prettier 3.9.6

The working tree was clean when the audit began. No source, dependency, CI, or configuration files were changed during the audit.

## Findings

### F-01 — Shared identity upserts cross ownership boundaries

**Severity:** High  
**Confidence:** High  
**Effort:** Medium to high  
**Reach:** Public intake, CRM lead creation, Contacts, Organizations, ownership/privacy

**Locations:**

- `app/Actions/CreateLeadFromIntake.php:25`
- `app/Actions/CreateLeadFromIntake.php:84`
- `app/Actions/CreateLead.php:26`
- `app/Actions/CreateLead.php:30`
- `tests/Feature/PublicIntakeTest.php:130`

**Evidence:**

- Public intake calls `Contact::updateOrCreate()` using only email, then overwrites name, phone, and organization.
- Public intake calls `Organization::firstOrCreate()` by website or name without an authenticated visibility boundary.
- Staff lead creation also upserts Contact by email and Organization by name without first proving that an existing record is visible to the acting user.
- The existing repeat-inquiry test explicitly confirms that a repeated email updates the existing Contact.

**Impact:**

An anonymous submission can overwrite an existing CRM Contact. A Sales/Ops user can resolve an organization outside their visibility scope by name and attach a new lead or contact to it. This is a data-integrity and privacy boundary problem, not merely a deduplication preference.

**Recommendation:**

Define an explicit identity policy before refactoring:

- Public intake should not globally mutate an existing CRM Contact based only on an unverified email.
- Staff workflows should select or resolve existing Organizations only through visibility-scoped identifiers, preferably UUIDs from authorized options.
- Consider storing an immutable intake snapshot on the Lead rather than treating an anonymous submission as an authoritative CRM update.
- Preserve intentional duplicate prevention only where the identity and ownership policy makes it safe.

**Suggested verification:**

- Submit a public inquiry whose email matches a Contact belonging to another organization and assert that the protected Contact is unchanged.
- Have a Sales/Ops user create a lead using the name of another user's Organization and assert that the hidden record is neither selected nor mutated.
- Replace the current repeat-inquiry expectation with the approved deduplication behavior.

### F-02 — Organization and Contact restore controls use the wrong HTTP verb

**Severity:** High  
**Confidence:** High  
**Effort:** Low  
**Reach:** CRM organization and contact detail pages

**Locations:**

- `routes/web.php:44`
- `routes/web.php:48`
- `resources/js/pages/Crm/Organizations/Show.vue:45`
- `resources/js/pages/Crm/Contacts/Show.vue:48`

**Evidence:**

The backend restore routes are DELETE routes, while the Organization and Contact Vue pages pass the restore URL as a link destination. The Lead page already uses `router.delete(restore(...).url)` correctly.

**Impact:**

Users receive a 405 or an otherwise failed restore action.

**Recommendation:**

Use `router.delete()` for both controls and add a browser regression test that archives each record, opens its detail page, clicks Restore, and verifies the record is restored.

**Suggested verification:**

- Direct GET to each restore endpoint is rejected.
- DELETE succeeds for an authorized user.
- Browser interaction completes without a 405 and shows the restored state.

### F-03 — Follow-up reminders are marked sent before queued delivery succeeds

**Severity:** High  
**Confidence:** High  
**Effort:** Medium  
**Reach:** Reminder jobs, queue retries, operational follow-up

**Locations:**

- `app/Jobs/SendFollowUpTaskReminder.php:76`
- `app/Jobs/SendFollowUpTaskReminder.php:78`
- `app/Notifications/FollowUpTaskReminder.php:20`
- `tests/Feature/JobsOperationsTest.php:61`

**Evidence:**

`FollowUpTaskReminder` implements `ShouldQueue`. The outer job queues the notification and immediately writes `reminder_sent_at`.

The current test uses `Notification::fake()`, which proves that the notification was requested but does not prove that the queued mail was delivered successfully.

**Impact:**

A failed notification can leave the task marked as sent. The scheduler then skips it on future runs, so the reminder may never be delivered.

**Recommendation:**

Choose one clear delivery-state model:

- Send the notification synchronously inside the retryable outer job and mark sent only after the send returns successfully; or
- Keep a separate queued notification but record an explicit pending/delivered state from the notification delivery lifecycle.

Keep the existing retry, timeout, uniqueness, and lock behavior aligned with that state model.

**Suggested verification:**

- A deliberately failing mail/notification transport leaves `reminder_sent_at` null and allows retry.
- A successful delivery sets the timestamp once.
- A missing recipient remains unsent and produces the expected operational warning.

### F-04 — Soft-deleted Contacts can break Lead rendering

**Severity:** High  
**Confidence:** High  
**Effort:** Low to medium  
**Reach:** Lead detail route, LeadResource, Lead Vue page

**Locations:**

- `app/Models/Lead.php:76`
- `app/Http/Resources/LeadResource.php:34`
- `resources/js/pages/Crm/Leads/Show.vue:37`
- `resources/js/types/crm.ts:16`

**Evidence:**

The Lead Contact relationship excludes soft-deleted Contacts by default. The resource dereferences `$this->contact->uuid`, and the Vue page assumes `lead.contact` is always present.

**Impact:**

A historically valid Lead can fail during serialization or render as a blank/error page after its Contact is soft-deleted.

**Recommendation:**

Decide whether CRM history should display archived Contacts. If yes, make the Lead relation soft-delete-aware with `withTrashed()` and expose an archived indicator. If no, make the resource and Vue type explicitly nullable and provide a safe historical fallback.

**Suggested verification:**

- Soft-delete the Contact for a visible Lead.
- Request the Lead detail page.
- Assert the Inertia response remains valid and the browser displays a clear archived/missing-contact state.

### F-05 — Related counts bypass visibility scoping

**Severity:** Medium  
**Confidence:** High  
**Effort:** Medium  
**Reach:** Organization and Contact list/detail pages

**Locations:**

- `app/Queries/OrganizationQuery.php:25`
- `app/Queries/ContactQuery.php:25`
- `app/Http/Controllers/Crm/OrganizationController.php:81`
- `app/Http/Controllers/Crm/ContactController.php:73`

**Evidence:**

The parent Organization or Contact query is scoped with `visibleTo($user)`, but related `withCount()` and `loadCount()` calls count all related records.

**Impact:**

Sales/Ops users may infer the existence or volume of another user's Contacts or Leads through counts even when those records are not visible.

**Recommendation:**

Apply the same visibility predicate inside each related count. Administrators may receive complete counts; restricted users should receive only counts for records they can view.

**Suggested verification:**

- Create visible, unassigned, and out-of-scope related records.
- Assert Sales/Ops counts include only visible records.
- Assert administrators see the complete count.

### F-06 — CRM filters cross the controller/query boundary as raw arrays

**Severity:** Medium  
**Confidence:** High  
**Effort:** Medium  
**Reach:** Organization, Contact, Lead, and Activity Log indexes

**Locations:**

- `app/Http/Controllers/Crm/OrganizationController.php:29`
- `app/Http/Controllers/Crm/ContactController.php:28`
- `app/Http/Controllers/Crm/LeadController.php:32`
- `app/Queries/OrganizationQuery.php:14`
- `app/Queries/ContactQuery.php:14`
- `app/Queries/LeadQuery.php:14`
- `app/Queries/ActivityLogQuery.php:85`

**Evidence:**

Controllers pass `$request->only(...)` arrays to query objects. Query builders accept `array<string, mixed>`. Boolean, identifier, enum, sort, and direction normalization is distributed through query closures.

The repeated `archived` handling also relies on truthiness, which makes string values such as `"0"` a correctness risk.

**Impact:**

Filter contracts are weakly typed, normalization is inconsistent, and business rules are harder to test or reuse.

**Recommendation:**

Use:

```text
Form Request validation → typed filter DTO → Query object → Eloquent
```

Normalize booleans, UUIDs, enum values, sort fields, and direction in the request/DTO boundary. Keep query objects focused on typed values and SQL construction.

**Suggested verification:**

- Add request tests for scalar, array, missing, invalid, and false-like filter values.
- Add query tests for archived/unarchived behavior and visibility.
- Add an architecture check preventing query `build()` methods from accepting untyped, `array`, or `mixed` filter payloads.

### F-07 — Multiple state-changing workflows bypass Actions

**Severity:** Medium  
**Confidence:** High  
**Effort:** Medium to high  
**Reach:** CRM lifecycle, notes, follow-up tasks, admin settings, user management

**Locations sampled:**

- `app/Http/Controllers/Crm/OrganizationController.php:108`
- `app/Http/Controllers/Crm/ContactController.php:100`
- `app/Http/Controllers/Crm/FollowUpTaskCompletionController.php:23`
- `app/Http/Controllers/Crm/LeadNoteController.php:18`
- `app/Http/Controllers/Crm/FollowUpTaskController.php:16`
- `app/Http/Controllers/Admin/AdminUserController.php:73`
- `app/Http/Controllers/Admin/BusinessSettingController.php:28`

**Evidence:**

Some controllers directly update, delete, or create records while newer create/update workflows already use dedicated Actions.

**Impact:**

Transactions, audit behavior, authorization, reuse, and regression coverage can vary across similar workflows.

**Recommendation:**

Extract coherent state-changing workflows first:

- Archive/restore CRM records.
- Complete follow-up tasks and create the associated activity.
- Add/delete notes where policy or transaction behavior matters.
- Update business settings or administrative user state where side effects exist.

Do not create an Action merely to wrap one trivial assignment with no business boundary.

**Suggested verification:**

Keep feature tests behavior-focused. Treat the Action boundary as a manual architecture review until the project has a stable list of lifecycle workflows; a broad static rule would create false positives.

### F-08 — Audit page will scale poorly without query/index changes

**Severity:** Medium  
**Confidence:** High  
**Effort:** Medium  
**Reach:** Admin audit page, `activity_log` table, future audit retention

**Locations:**

- `database/migrations/2026_06_15_165821_create_activity_log_table.php:22`
- `app/Queries/ActivityLogQuery.php:29`
- `app/Queries/ActivityLogQuery.php:95`
- `config/activitylog.php:57`

**Evidence:**

- The activity table has indexes for `log_name`, `subject`, and `causer`, but not `created_at`.
- The audit query orders by `created_at` and filters by date ranges.
- Filter catalogs perform several distinct database queries on every page request.
- The audit page is paginated, but its option catalogs are not bounded or cached.

**Impact:**

As the audit table grows, page loads and filter-option generation will consume increasing database resources.

**Recommendation:**

- Add and verify a `created_at` index.
- Cache low-volatility event, causer, and subject option catalogs with a short TTL.
- Keep the activity table as the canonical recent searchable store.
- Archive older records to private object storage through an idempotent queued process when volume justifies it.
- Revisit the `activitylog` buffer setting if individual requests begin producing many audit records.

Do not move the canonical audit history to Redis merely to avoid adding the appropriate relational index.

### F-09 — Frontend type guardrails are permissive

**Severity:** Low  
**Confidence:** High  
**Effort:** Low to medium  
**Reach:** TypeScript and Vue maintainability

**Locations:**

- `eslint.config.ts:14`
- `eslint.config.ts:15`
- `resources/js/composables/useFillInObjects.ts:1`
- `resources/js/components/UserMenu.vue:122`

**Evidence:**

ESLint disables `@typescript-eslint/no-explicit-any` and `@typescript-eslint/no-unused-vars`. Enabling `no-explicit-any` temporarily reports two errors in `useFillInObjects.ts`; enabling `no-unused-vars` reports eight existing unused variables, several intentionally underscore-prefixed.

**Impact:**

Unsafe assertions and implicit unused code can accumulate while `npm run lint:check` remains green.

**Recommendation:**

- Replace `any` with `unknown`, constrained generics, or explicit interfaces.
- Replace unsafe assertions in UI components with typed props or discriminated unions.
- Enable the rules incrementally, using narrowly scoped underscore-variable exceptions where intentional.

**Suggested verification:**

- Run the stricter rules in warning mode first.
- Fix the measured baseline.
- Promote them to errors and keep `npm run typecheck` as a separate required check.

### F-10 — Roadmap and user-testing bookkeeping are inconsistent

**Severity:** Low  
**Confidence:** High  
**Effort:** Low  
**Reach:** Planning, user-testing queue, completion reporting

**Locations:**

- `docs/development/active-roadmap.md`
- `config/user-testing.php`
- `tests/Feature/UserTestingPageTest.php`

**Evidence:**

The quality/launch documentation referenced a Lead detail task that was not represented consistently in the active roadmap and user-testing mapping.

**Impact:**

Work may be reported under stale task numbers, and user-testing status can drift from the actual roadmap.

**Recommendation:**

Reconcile the live roadmap, completed roadmap, decision points, and `config/user-testing.php`. Keep `tested_at`, `roadmap_task_at`, and `approved_at` as separate state transitions.

**Status (2026-07-22):** Reconciled while consolidating the development documents. The active roadmap now owns the current task sequence, and Lead detail rendering remains tracked by F-04 in this audit rather than by a nonexistent Task 22.

**Suggested verification:**

Review the affected mapping manually whenever roadmap tasks are renumbered. Do not use a text-only automated check as the source of truth for prose planning documents.

### F-11 — CRM option lists are currently unbounded

**Severity:** Low today  
**Confidence:** Medium  
**Effort:** Medium  
**Reach:** Organization, Contact, and Lead create/edit pages

**Locations:**

- `app/Http/Controllers/Crm/ContactController.php:33`
- `app/Http/Controllers/Crm/ContactController.php:56`
- `app/Http/Controllers/Crm/OrganizationController.php:34`
- `app/Http/Controllers/Crm/LeadController.php:39`

**Evidence:**

Create/edit pages load all visible organizations or active owners into the Inertia response.

**Impact:**

Payload size and page rendering cost will grow with CRM volume.

**Recommendation:**

When record volume justifies it, replace full option lists with searchable, paginated, or deferred selectors. Keep the server-side visibility scope on the option endpoint/query.

**Suggested verification:**

Add an option-load test with a realistic record count and measure response size before introducing a more complex selector workflow.

## Automated guardrails

| Guardrail ID | Finding | Classification | Proposed tool | Observable contract |
|---|---|---|---|---|
| `PST-CRM-IDENTITY-001` | F-01 | Test candidate plus policy gate | Pest Feature | Public and staff identity resolution cannot cross ownership or mutate protected records |
| `PST-CRM-RESTORE-001` | F-02 | Test candidate | Pest Browser and Feature | Restore controls use DELETE transport and complete successfully |
| `PST-JOBS-REMINDER-001` | F-03 | Test candidate | Pest Feature | Failed delivery leaves the reminder retryable and unsent |
| `PST-CRM-SOFTDELETE-001` | F-04 | Test candidate | Pest Feature and Browser | Lead detail remains renderable when its Contact is soft-deleted |
| `PST-CRM-COUNTS-001` | F-05 | Test candidate | Pest Feature | Related counts respect the viewer's visibility scope |
| `ARCH-QUERY-001` | F-06 | Custom architecture test | Existing Pest `ArchTest.php` | Query `build()` methods accept typed filter DTOs, not raw arrays or mixed values |
| `ARCH-ACTION-001` | F-07 | Manual review initially | Manual review, possible later PHPStan rule | Only stable, named state-changing workflows are required to use Actions |
| `DB-AUDIT-INDEX-001` | F-08 | Migration/operational check | Schema review and EXPLAIN | Audit date ordering/filtering has a justified index and acceptable query plan |
| `TS-STRICT-ANY-001` | F-09 | Configuration opportunity | ESLint | Explicit `any` is rejected except for documented, narrow exceptions |
| `TS-STRICT-UNUSED-001` | F-09 | Configuration opportunity | ESLint | Unused values are rejected with intentional underscore exceptions only |
| `DOC-ROADMAP-001` | F-10 | Manual release check | Roadmap reconciliation | Live roadmap and user-testing mapping agree before status changes |
| `PERF-CRM-OPTIONS-001` | F-11 | Deferred | Feature test and payload measurement | Option payloads remain bounded as CRM volume grows |

### Guardrail design notes

#### `PST-CRM-IDENTITY-001`

This guardrail cannot be finalized until the public-intake deduplication policy is chosen. The current repeat-inquiry test encodes behavior that may be unsafe. The test should fail if an anonymous submission overwrites a protected Contact or resolves a hidden Organization.

#### `PST-CRM-RESTORE-001`

Archive Organization and Contact records, visit their detail pages, activate Restore, and assert both the successful response and the cleared archive state. A direct GET request should not be treated as a valid restore path.

#### `PST-JOBS-REMINDER-001`

Use a deliberately failing notification/mail transport rather than `Notification::fake()` alone. Assert that the exception remains retryable and `reminder_sent_at` remains null. A successful delivery should set the timestamp once.

#### `PST-CRM-SOFTDELETE-001`

Soft-delete a Contact related to a visible Lead. Assert a valid Inertia response and a clear archived/missing-contact presentation. The test should match the chosen product behavior: `withTrashed()` historical display or an explicit nullable fallback.

#### `PST-CRM-COUNTS-001`

Create visible, unassigned, and out-of-scope related records. Assert restricted users receive only visible counts while administrators receive complete counts.

#### `ARCH-QUERY-001`

Extend the existing reflection-based Action signature check to `app/Queries/*Query.php`. Require typed filter objects for `build()` methods and reject native `array`, `mixed`, and missing parameter types. Pair this with request tests because a signature rule cannot prove that values were normalized correctly.

#### `TS-STRICT-ANY-001` and `TS-STRICT-UNUSED-001`

Enable the rules in warning mode, fix the measured baseline, then promote them to errors. Configure only narrow underscore-prefixed exceptions for intentionally unused values; do not disable the rules globally.

## Existing tooling corrections

- `phpstan.max.neon` currently analyzes only `app/Actions` and `app/Data`; it does not protect query/controller filter boundaries.
- `tests/Feature/ArchTest.php` protects debug statements, Validator usage, and Action handler signatures, but not query signatures or state-changing controller boundaries.
- `composer test:lint` currently fails because Rector reports four existing dry-run changes in:
  - `app/Http/Controllers/Settings/ProfileController.php`
  - `app/Http/Requests/StorePublicIntakeRequest.php`
  - `app/Notifications/FollowUpTaskReminder.php`
  - `app/Queries/ActivityLogQuery.php`
- The full no-coverage test run completed with 169 passing tests and one failing `LaunchSmokeTest` assertion involving the initial intake field.
- `composer test` could not complete locally because no coverage driver was available; CI configures Xdebug.
- Frontend typecheck and lint passed under the current permissive configuration.
- No custom PHPStan rules, Deptrac, PHPArkitect, or Semgrep setup is currently present. Avoid adding dependencies solely to enforce broad architecture opinions.

## Recommended implementation order

### Now

1. Decide and document the F-01 identity/deduplication policy.
2. Fix F-02 restore transport and add browser coverage.
3. Fix F-04 soft-deleted Contact handling and add regression coverage.
4. Fix F-05 visibility-scoped counts and add Sales/Ops/Admin tests.
5. Fix F-03 reminder delivery state and add a real failure-path test.

### Next

1. Introduce typed filter DTOs for CRM and audit queries.
2. Add `ARCH-QUERY-001` after the DTO refactor.
3. Add the `activity_log.created_at` index and cache bounded audit option catalogs.
4. Extract coherent lifecycle Actions from the most important direct controller writes.
5. Enable stricter frontend ESLint rules after clearing the baseline.

### Later

1. Reconcile roadmap and user-testing documentation.
2. Replace unbounded CRM option lists when record volume justifies it.
3. Add an idempotent S3 audit archive with manifests, checksums, restore rehearsals, and a defined searchable-history experience.

## Audit storage and future load direction

Moving the canonical audit log to Redis is not recommended. Redis can be useful for caching audit filter catalogs or as part of a temporary processing buffer, but it should not be the only durable audit record.

A safer progression is:

```text
MySQL: recent searchable audit records
    ↓ queued, idempotent archive process
S3: partitioned, private, immutable long-term audit history
```

Recommended storage behavior:

- Keep recent searchable audits in MySQL.
- Add the missing `created_at` index before changing storage systems.
- Cache low-volatility event, causer, and subject option catalogs in Redis.
- Archive older records to S3 as partitioned JSON Lines or Parquet with manifests, counts, checksums, and retry-safe deletion.
- Use S3 lifecycle transitions for long-term cost control.
- Use S3 Object Lock only if the audit-retention policy requires immutability or WORM-style protection.
- Use a dedicated analytical/search store such as OpenSearch, ClickHouse, or Athena over S3 if multi-year interactive search becomes a requirement.

Redis logical databases do not provide true failure or persistence isolation. Separate Redis connections or instances are more appropriate when workloads need independent capacity and recovery characteristics.

## Validation performed

The following checks were run during the audit:

```text
php artisan test --compact tests/Feature/PublicIntakeTest.php tests/Feature/Crm/EntityWorkspaceTest.php tests/Feature/Crm/LeadWorkspaceTest.php tests/Feature/JobsOperationsTest.php
36 passed, 248 assertions

npm run typecheck
passed

npm run lint:check
passed

composer test:types
passed

composer test:lint
failed on four existing Rector dry-run suggestions

php artisan test --compact tests/Browser/CrmDetailSmokeTest.php
4 passed, 33 assertions

composer test
stopped because no local coverage driver was available

php artisan test --compact
169 passed, 1 failed in LaunchSmokeTest

git diff --check
passed
```

## Limitations

- No production traffic, database size, or query-plan measurements were available.
- The connected database contained 158 activity records, which is not a meaningful forecast of production scale.
- Redis persistence, eviction, failover, and restore behavior were not load-tested.
- Mail-provider acceptance and queued notification delivery were not tested against a real provider.
- No full browser review was repeated for every affected CRM state.
- Recommendations involving retention duration, immutable storage, and public-intake identity require explicit product/operations decisions.

## Quality, launch, and later-module checklist

### Goals

Harden the application for daily use, prove the critical flows, and prepare deferred modules without prematurely building them.

### Launch gate

The app is ready for v1 launch only when these conditions are true:

- Public marketing and lead intake work without authentication.
- Authenticated CRM workflows cover lead list/detail, create/edit/archive/restore, notes, activities, tasks, ownership,
  and lead-to-client conversion.
- Admin workflows cover users, roles, permissions, settings, jobs access, and audit review.
- All authenticated feature routes have policy or middleware coverage and unauthorized direct URL access is tested.
- No visible demo data, external demo APIs, placeholder GitHub links, stale starter-kit copy, or generic Nuxt dashboard
  positioning remains in production-facing UI.
- Queue, scheduler, Horizon, failed-job, notification, backup, error-tracking, and ownership expectations are documented
  for the selected Laravel Forge deployment target.
- Critical data handling choices from `docs/development/active-decision-points.md` that affect launch are resolved or
  explicitly deferred in the roadmap.

### Test coverage

- [x] Ensure `composer test` passes with all feature and unit tests.
- [x] Ensure `composer test:types` passes after all PHP domain additions.
- [x] Ensure `composer test:lint` passes before merge.
- [x] Ensure `npm run build` passes after all frontend work.
- [x] Add browser smoke tests for public marketing, login, dashboard, CRM lists, admin users, roles, jobs, settings,
  audit pages, and expressive form controls.
- [ ] Restore separate CRM organization/contact detail browser smoke coverage. Pest's temporary HTTP server currently
  blank-renders CRM detail URLs; this is deferred to Roadmap Task 21. Lead detail rendering remains tracked by F-04 in
  this audit.
- [x] Add regression tests for unauthorized direct URL access.
- [x] Add tests for queue dispatch, notification dispatch, and activity logging.

Browser smoke coverage uses Pest's browser plugin with Playwright Chromium. The Browser PHPUnit suite runs through
`composer test:browser` and is included in `composer test`; the CI workflow installs the Chromium runtime before
running the quality contract.

### UX polish

- [x] Replace all empty/demo states with Southeast Code-specific operational empty states.
- [x] Review loading, error, validation, success, and destructive-confirmation states across CRM/admin/jobs/audit.
- [x] Verify responsive behavior for marketing pages and authenticated workspaces.
- [x] Ensure dense tables remain usable on smaller screens through responsive columns, drawers, or detail pages.
- [x] Review keyboard navigation and focus states.
- [x] Verify icons are consistently sourced from Lucide-Vue where custom icons are needed.
- [x] Verify shared components are extracted rather than duplicated across CRM and admin.

### Security and privacy hardening

- [x] Review auth, registration, password reset, email verification, and 2FA behavior.
- [x] Confirm route and policy coverage for every authenticated feature.
- [x] Confirm contact form rate limits and spam controls.
- [x] Confirm sensitive fields are not logged in activity logs.
- [x] Confirm file uploads are disabled or secured if introduced.
- [x] Confirm production cookies, trusted proxies, HTTPS, and session settings.
- [x] Add a privacy policy and contact-data consent for public intake. Add terms only if DP-12 requires them.

### Data and operational readiness

- [x] Verify migrations run cleanly on a fresh database.
- [x] Verify seeders produce useful local/demo data without polluting production system data.
- [x] Verify backups and restore expectations are documented.
- [x] Verify queue workers, scheduler, and Horizon are documented for deployment.
- [x] Verify failed-job alerting and operational visibility.
- [x] Verify admin users and initial roles can be bootstrapped safely.

### Documentation

- [x] Update README with current setup, architecture, commands, and deployment notes.
- [x] Keep `docs/development/active-roadmap.md` current as phases are completed.
- [x] Add ADRs for completed decision points that change the roadmap defaults.
- [x] Add admin/user documentation for CRM lifecycle, user management, roles, permissions, jobs, and audit logs.
- [x] Add runbooks for queues, failed jobs, Horizon, backups, and common deployment issues.

### Later modules to revisit

- Client portal for project status, shared files, notes, and approvals.
- Proposal workflow with templates and approval states.
- Statement-of-work and contract workflows.
- Invoice and payment/accounting integrations.
- Advanced reporting dashboards and saved report views.
- Email campaign/newsletter tooling.
- Knowledge base or internal SOP section.
- Public case studies and portfolio management.
- Import/export pipelines for CRM data.

### Acceptance criteria

- A fresh database can be migrated and seeded into a useful local state without production-only data.
- Required user roles can be bootstrapped safely, and at least one super admin path is documented.
- The public intake, CRM CRUD/conversion, admin users/roles, jobs access, settings update, and audit-log review flows
  are covered by focused Pest tests.
- Browser smoke coverage exists for critical auth, marketing, CRM, admin, jobs, settings, and audit pages when practical;
  otherwise the roadmap records why it is deferred.
- Responsive behavior is verified for marketing, authenticated navigation, dense CRM/admin tables, and forms.
- Security hardening is reviewed for registration, email verification, 2FA, session/cookie settings, trusted proxies,
  HTTPS, contact-form rate limits, and activity-log redaction.
- README, roadmap, deployment notes, queue/Horizon runbook, ADRs, and admin/user docs match the shipped behavior.
- Later modules remain documented as later work and do not leak half-built navigation or broken routes into v1.

### Verification

- User-run verification before launch:
  - `composer test`
  - `composer test:types`
  - `composer test:lint`
  - `npm run build`
  - targeted Pest filters for public intake, CRM permissions, admin permissions, jobs/Horizon access, settings, and audit
