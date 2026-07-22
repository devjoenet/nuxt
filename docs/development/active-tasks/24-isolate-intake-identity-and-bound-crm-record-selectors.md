# Task 24 — Isolate Intake Identity And Bound CRM Record Selectors

Combine F-01 and F-11. Public intake must create isolated unowned identity records, while staff uses visibility-scoped UUID selection and bounded searchable CRM option endpoints.

- [ ] Every anonymous submission creates a new unowned Contact and Lead; it never reuses/mutates an existing contact or organization.
- [ ] Lead retains immutable intake identity/company snapshot data; no organization is linked until staff resolves it.
- [ ] Staff selectors use visibility-scoped UUID lookup and bounded asynchronous options, never complete CRM catalogs.

## Implementation instructions

1. Begin with focused public-intake, lead, entity-workspace, and browser tests. Document current contact/organization matching paths and verify the accepted identity policy above. Keep staff-authenticated workflows separate from anonymous intake.
2. Add an `intake_snapshot` JSON column to leads through a reversible migration and cast. Store only the submitted intake identity/company fields needed for history; exclude it from activity-log attribute changes and do not log raw sensitive intake values.
3. Refactor the public Form Request → typed DTO → single-purpose Action flow. The Action must always create a new unowned Contact and a new Lead with snapshot data and no organization link. Remove public global matching/upsert logic. Preserve validation, rate limiting, redirects, and notification behavior.
4. For staff create/edit paths, accept only UUID IDs, resolve records through visibility-scoped queries, and return concealment-consistent validation/404 behavior for inaccessible records. Never expose global email/name matching as a selector shortcut.
5. Add bounded, visibility-scoped search endpoints for contact/organization options (maximum 20 records, minimum query length, deterministic labels/UUID values, explicit loading/empty states). Use Form Request validation, typed query/action boundaries, Resources, Wayfinder `.url`, and Inertia `useHttp`/Nuxt UI selector conventions. Remove complete option collections from initial page props while retaining selected values after validation failure/edit and explicit create-new flows.
6. Test anonymous duplicate submissions, existing staff-owned identities, unowned contacts, inaccessible UUIDs, immutable snapshots, no activity-log leakage, endpoint bounds/visibility, selector keyboard behavior, and clean console output. Use factory state, not browser-created fixtures or sleep.
7. Run focused tests, fresh migration, Pint, PHP types/lint, frontend type/lint/format/build, full `composer test`, and `git diff --check`. Move only after anonymous and authenticated identity flows pass.

```markdown
## Summary
- describe isolated public identity creation and immutable intake snapshots
- describe visibility-scoped UUID selection and removal of global staff upserts
- describe bounded searchable CRM selectors and preserved create-new behavior

## Verification
- list every focused and full quality-contract command actually run, with its result
```
