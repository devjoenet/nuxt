# Task 22 — Preserve Lead History When A Contact Is Deleted

Resolve F-04 without resurrecting deleted CRM records: lead history must retain its historical contact identity while marking that contact as deleted and avoiding a broken record link.

- [ ] `Lead::contact()` includes soft-deleted contacts.
- [ ] Lead API/TypeScript payload includes the contact deletion state.
- [ ] Lead UI shows historical identity and a clear deleted-contact state rather than a live link.

## Implementation instructions

1. Start with `php artisan test --compact tests/Feature/Crm/LeadWorkspaceTest.php` and `CrmDetailSmokeTest.php`. Create an owned/visible lead with a soft-deleted contact and confirm the current relationship is absent. Preserve visibility rules: a deleted contact remains historical data only, never an authorization bypass.
2. Change only `Lead::contact()` to `belongsTo(Contact::class)->withTrashed()`. Keep soft deletion and route binding unchanged.
3. Extend the lead resource with the contact's `deleted_at` state and update `resources/js/types/crm.ts`. Do not add intake PII or expose unrelated contact data.
4. In the lead workspace/detail, show name/email as historical identity. Link to the contact only when it is not deleted and visible; otherwise render a visible `Deleted contact` status with no route target. Preserve normal empty-contact fallback.
5. Extend `LeadWorkspaceTest` and `CrmDetailSmokeTest` for visible deleted contacts, normal contacts, and hidden/out-of-scope records. Assert no broken href, correct labels, and no visibility leak.
6. Run focused tests, Pint if PHP changed, frontend type/lint/format/build, full `composer test`, and `git diff --check`. Then complete the authorized `crm-leads-show` Task 22 user-testing handoff, preserving existing notes/timestamps and leaving approval null.

```markdown
## Summary
- describe historical lead-contact visibility after soft deletion
- describe the explicit deleted-contact UI state and preserved authorization
- describe focused resource, feature, browser, and user-testing coverage

## Verification
- list every command actually run, with its result
```
