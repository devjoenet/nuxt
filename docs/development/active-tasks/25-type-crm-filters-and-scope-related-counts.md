# Task 25 — Type CRM Filters And Scope Related Counts

Combine F-05 with the CRM portion of F-06. Parent records, related counts, input validation, and query objects must all obey the same viewer visibility rule.

- [ ] Organization/Contact related counts use `visibleTo($user)` for restricted users.
- [ ] CRM index filters use Form Requests, typed immutable DTOs, and typed sort/filter enums.
- [ ] False-like archived values and all filter data are normalized before SQL construction.

## Implementation instructions

1. Run `EntityWorkspaceTest` and `LeadWorkspaceTest`. Build owned, unassigned, and another-user relation fixtures to demonstrate the current count leak while retaining current URL/query-string shapes for all three indexes.
2. Add authorized Organization, Contact, and Lead index Form Requests. Validate trimmed search, Task 24 UUID filters, active numeric owner IDs, boolean `archived`, allowed sort/direction, and existing Lead status/source enums. Normalize missing/`0`/`'0'`/`false` archived inputs to native `false` in the request; never rely on PHP string truthiness.
3. Add immutable `app/Data` DTOs (one per index) with native nullable search/identifiers, real boolean archive state, typed Lead enums, and typed sort/direction enums. Construct only from normalized validated input. Queries must not receive `Request`, global request state, or `array<string, mixed>`.
4. Change `OrganizationQuery::build`, `ContactQuery::build`, and `LeadQuery::build` to accept the authenticated User plus the corresponding DTO. Keep typed Eloquent builders, stable defaults, DTO enum `orderBy` values, and one archived comparison.
5. Apply model `visibleTo($user)` scopes inside every relevant `withCount`/`loadCount` closure for Organization contacts/leads and Contact leads. This retains full Admin/Super Admin counts naturally and aligns detail collections/counts without controller owner-ID duplication.
6. Keep controllers at authorize → DTO → Query → paginate/resource/filter props. Preserve page size, URLs, pagination, Inertia prop shape, and direct-route concealment.
7. Extend both feature suites for absent/scalar/array/invalid/false-like data, validation failures, restricted-user counts, administrator totals, index/detail payloads, and direct URL 404s. Inspect SQL only if a test demonstrates a performance concern; do not add caching/indexes here.
8. Run focused tests, Pint, PHP types/lint, frontend typecheck, full `composer test`, and `git diff --check`, then move Task 25 when all role/count/filter cases pass.

```markdown
## Summary
- describe typed CRM filter request/DTO/query boundaries and normalized false-like values
- describe visibility-scoped Organization and Contact related counts
- describe restricted-user, administrator, validation, and regression coverage

## Verification
- list every focused and full quality-contract command actually run, with its result
```
