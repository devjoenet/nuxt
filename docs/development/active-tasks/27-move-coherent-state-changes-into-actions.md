# Task 27 — Move Coherent State Changes Into Actions

Resolve F-07 after the preceding correctness boundaries stabilize. Extract real workflows, not a generic service/repository layer or Actions that merely rename one assignment.

- [ ] CRM lifecycle/note/follow-up, admin-user, and business-setting workflows use typed Actions.
- [ ] Form Requests/controllers retain transport validation/authorization; Actions own invariants, transactions, and paired effects.
- [ ] The same state-changing boundary works outside HTTP while preserving audit, redirects, concealment, and idempotency.

## Implementation instructions

1. Run `EntityWorkspaceTest`, `LeadWorkspaceTest`, and `AdminAuthorizationTest`; inventory confirmed F-07 controller writes and classify real workflows versus trivial transport assignments. Do not sweep unrelated code.
2. Extract transaction-backed `CompleteFollowUpTask` (task + Lead activity), `CreateLeadNote` (note + explicit actor activity), and `CreateFollowUpTask` (consistent Lead-owner assignment), with small typed DTOs from existing Form Requests. Pass `User`; do not use `auth()->id()` inside domain code.
3. Extract `UpdateAdminUserAccess` with `AdminUserAccessData`, owning Super Admin protection, self-deactivation, timestamps, role sync, transaction, and activity logging. The Action must enforce invariants outside HTTP.
4. Extract `UpdateBusinessSetting` with typed data and actor, updating the singleton, detecting changed fields, and writing its activity in one boundary.
5. Introduce named Organization/Contact/Lead archive/restore/delete Actions only where a stable lifecycle transition is reused: set/clear `archived_at`, soft-delete, return refreshed model, and pass actor when auditing. Keep policy/visibility concealment at HTTP boundary. Do not create `setArchived($model, bool)`, `CrmService`, or per-Action interfaces.
6. Make each controller read authorize/validate → DTO → Action → redirect, with explicit injection and unchanged routes/verbs/Wayfinder/flash text. Preserve Task 21 DELETE restore transport.
7. Add direct Action tests for transaction/invariant behavior; retain feature tests for authorization, validation, redirects, persistence, and observed activities. Prove rollback, repeat-completion semantics, protected Super Admin rules, and hidden-record mutation denial without mocking Eloquent.
8. Review `ArchTest.php` but do not add a broad controller-mutation static rule. Run focused tests/new Action tests, Pint, PHP types/lint, full `composer test`, and `git diff --check`; confirm activity data remains redacted as required.

```markdown
## Summary
- describe the typed Actions extracted for CRM, admin-user, and business-setting workflows
- describe preserved authorization, invariants, transactions, audit behavior, and controller responses
- describe direct Action and behavior-focused feature coverage

## Verification
- list every focused and full quality-contract command actually run, with its result
```
