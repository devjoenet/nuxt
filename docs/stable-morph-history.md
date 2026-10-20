# Stable morph aliases — Task 03

Live task: `01a15abd-361d-720f-a36d-b6f48c8b64d6`. Branch: `feature/stable-morph-history`.
Base: `0ed92161cb2faa2f28bc2d71549a7fbc8ad91c83` (Task 02 merged and its live record completed before work started).

## Fresh consumer bootstrap

Set `MODULES_STABLE_MORPHS=true` **before the first boot, migration or seed** of an empty, isolated candidate. `ModuleServiceProvider` contributes the installed models through `ModuleRegistry`, freezes registration when providers finish booting, then enforces the combined map before Artisan migrations/seeding or application requests execute. Config caching preserves the selected setting. Future package providers move their contributions with their owner; the bridge registry must not import domain models.

The current reference defaults to `false` and continues writing its existing class names. Do not enable this setting against the operational Development database, point a candidate at that connection, or mix the two writers. No migration, historical JSON conversion or abandoned namespace compatibility reader is supplied. The remaining class-name reader is the existing reference mode, not a new-install compatibility promise.

Configured user, Permission role/permission, and Activitylog activity model classes are resolved at provider boot. Configure these before boot, not halfway through a request. The registry rejects duplicate aliases, multiple aliases for one model, missing classes, non-Eloquent classes, and conflicts with a previously registered Laravel map before applying changes. Strict writes reject unmapped models. Maps contain only installed contributions; providers must not register absent package classes.

## Alias inventory and ownership

| Owner | Stable alias | Current model |
| --- | --- | --- |
| Foundation | `user` | Configured workspace User |
| Foundation | `business-setting` | BusinessSetting |
| Foundation infrastructure | `role`, `permission` | Configured Spatie Role and Permission |
| Audit adapter | `audit-activity` | Configured Activitylog Activity |
| CRM | `crm-organization`, `crm-contact`, `crm-lead` | Organization, Contact, Lead |
| CRM | `crm-follow-up-task`, `crm-note`, `crm-activity` | FollowUpTask, Note, CrmActivity |
| Development | `development-task`, `development-task-link`, `development-task-note` | DevelopmentTask, DevelopmentTaskLink, DevelopmentTaskNote |
| Development | `development-test-run`, `user-testing-page` | DevelopmentTestRun, retained legacy UserTestingPage |

Roles/permissions stay in the foundation bootstrap contribution so foundation-only consumers can provision an administrator without user-management UI. The underlying activity migration remains foundation-owned; its browsing/model contribution moves with the Audit adapter during extraction. An Activity model alias supports activities used as subjects; enabling it does not enable recursive activity logging.

All actual polymorphic storage found in the source migrations:

| Storage | Type/identity columns | Writer and reader contract |
| --- | --- | --- |
| `activity_log` subject | `subject_type`, `subject_id` | Model logging and explicit `performedOn`; history uses `getMorphClass()` |
| `activity_log` actor | `causer_type`, `causer_id` | Explicit actors/default causers; configured user filter uses its morph class |
| `model_has_roles` | `model_type`, `model_id` | Spatie assignment and role resolution use the same user alias |
| `model_has_permissions` | `model_type`, `model_id` | Direct grants and authorization use the same user alias |
| `notes` subject | `subject_type`, `subject_id` | Lead relation, factory and demo seeder use `getMorphClass()` |
| `activity_log.attribute_changes` JSON | `attributes.subject_type/subject_id`, `old.subject_type/subject_id` | Note logging records its aliased relation values; lead history searches both branches |

Other actor/owner columns (`owner_id`, `user_id`, Development submitter/reviewer/assignee, author/added-by, tester/requested-by, legacy tested-by) are ordinary foreign keys, not extra type columns. `role_has_permissions` is an ordinary role/permission pivot. The full migration inventory remains in [the frozen baseline](modular-baseline/source/schemas.tsv).

## History and unavailable types

Existing correct `RecordHistoryQuery`, lead-related JSON predicates, actor filters and recent-change filters retain `getMorphClass()`. Factories and demo notes no longer persist raw `Lead::class`. Audit/history labels resolve known aliases back to model names, keeping namespaces and alias prefixes out of the display.

`AuditMorphs` batch-loads only registered installed subjects/actors after the paginated query, setting unsupported relations to null without instantiating their stored type. Unknown subjects display `Unavailable record`; history keeps `Unavailable actor` for missing actors. Record visibility, audit permission, ordering and pagination still apply. The global audit endpoint remains restricted to audit viewers; unavailable types grant no new access to historical fields or routes. Recent changes still excludes unauthorized/unassociated CRM records.

Synthetic tests cover role/direct grants, explicit and configured actors, fresh note JSON, soft-deleted subjects, permanently deleted notes, changed note associations through old JSON, unsupported writes, absent subjects/actors, alias collisions and demo seeding. Six registry consumer combinations cover foundation, Audit, user management, CRM, Development and full composition. These are in-place registration/bootstrap proofs; independent Composer consumers, package archives and absent-module route removal remain extraction gates in Tasks 05–07.

## Reproduce focused verification

Normal reference-mode regression tests use the committed PHPUnit SQLite memory configuration. To also verify enforcement during provider boot rather than only within the test setup:

```bash
rtk proxy env MODULES_STABLE_MORPHS=true php artisan test --compact tests/Feature/StableMorphHistoryTest.php tests/Feature/LeadHistoryTest.php tests/Feature/RecordHistoryTest.php tests/Feature/AdminOverviewTest.php tests/Feature/AdminAuthorizationTest.php tests/Feature/Actions/AdminStateActionsTest.php tests/Feature/DevelopmentUserTestingWorkflowTest.php
```

For MySQL, create a dedicated empty disposable database, then pass `DB_CONNECTION=mysql` and its `DB_DATABASE` together with the same strict-alias flag. Existing connection credentials stay outside Git. Never use the reference database: the test runner rebuilds the selected test schema. Local checks used `nuxt_morph_task03_20261020`; this disposable schema is removed after verification. Tests create synthetic records only. No operational records are imported.

Results and remaining gates are recorded in the [delivery ledger](modular-baseline/README.md#delivery-ledger). Hosted CI, owner testing, merge, independent packages, staging and production are separate evidence.
