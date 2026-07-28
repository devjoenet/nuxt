# Task 32 — Preserve User-Testing State And Rename Its Permission

Resolve F-05. `UserTestingPageController` currently owns a multi-state write and replaces review, approval, and roadmap
timestamps every time their checkboxes stay checked. The same mutation is authorized by a permission whose name says
`view`.

- [ ] Notes-only and repeated saves preserve the original review actor/timestamp, approval timestamp, and roadmap
  timestamp while each state remains true.
- [ ] Explicit false-to-true and true-to-false transitions set or clear only the state they own; approval still implies
  tested.
- [ ] Form Request validation flows through an immutable DTO and one transaction-safe `UpdateUserTestingPage` Action.
- [ ] The existing permission is migrated in place to `development.user_testing.manage` without losing role assignments.
- [ ] Frozen-time Action and HTTP transition coverage proves repeated saves, different actors, clearing, and approval
  semantics.

## Implementation instructions

1. Run `UserTestingPageTest` and `AdminStateActionsTest`; record current first-save behavior and add failing repeated-save
   cases before refactoring. Use frozen time and a second actor to prove which timestamps and `tested_by` value currently
   drift.
2. Generate `UpdateUserTestingPage` with `php artisan make:class Actions/UpdateUserTestingPage --no-interaction` and a
   small immutable `UserTestingPageData` DTO using the existing `App\Data`/Spatie Data conventions. The DTO owns
   validated booleans plus normalized nullable notes; the Form Request owns transport validation and authorization.
3. In the Action, transactionally lock and reload the target page. Approval forces the effective tested state. For each
   true state, use its existing timestamp or set `now()` only on the first false-to-true transition; clear the timestamp
   only when that checkbox is explicitly false. Preserve the original `tested_by` while tested remains true, set it to
   the actor on a new tested transition, and clear it when tested becomes false. Keep notes trimming/null normalization
   in this boundary.
4. Reduce the controller update method to authorized Form Request → DTO → injected Action → existing redirect/flash.
   Keep index payload construction and polling reads out of the Action; do not create a generic review service or move
   HTTP response behavior into domain code.
5. Rename the capability to `DevelopmentUserTestingManage` /
   `development.user_testing.manage` in the enum, route middleware, Form Request, shared permission payload, navigation,
   seeder, and tests. Generate a data migration that renames the existing `permissions` row in place for the `web`
   guard so current role pivots retain their IDs; make it idempotent and clear Spatie's permission cache. Do not create a
   second permission when the application has no read-only user-testing role.
6. Add direct Action tests for first review, notes-only save, repeated save by another actor, roadmap transition,
   approval-implies-tested, clearing each state, and concurrent stale-model protection. Retain feature tests for
   validation, authorization, redirect/flash, polling payload, and removal of approved pages from the queue.
7. Run the focused Action/user-testing/admin tests, migration apply/rollback/reapply checks,
   `vendor/bin/pint --dirty --format agent`, both PHPStan levels, frontend typecheck/lint/format/build, the full Herd
   coverage contract, and `git diff --check`. Do not update real user-testing timestamps or approvals as verification
   fixtures.
8. Once completed, provide the user with a code block containing a PR summary (see below) for these changes.

```markdown
Implement Task 32: Preserve User-Testing State And Rename Its Permission

## Summary
- describe the typed user-testing transition Action and preserved timestamp/actor semantics
- describe the in-place manage-permission rename and retained role assignments
- describe the frozen-time Action and HTTP transition coverage

## Verification
- list every focused and full quality-contract command and migration check actually run, with its result
```
