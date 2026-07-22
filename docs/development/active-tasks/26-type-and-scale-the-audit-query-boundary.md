# Task 26 — Type And Scale The Audit Query Boundary

Combine F-08, the audit portion of F-06, and `ARCH-QUERY-001`. Add the audit date index, a typed filter boundary, cached low-volatility options, and a guardrail after Task 25 converts CRM Queries.

- [ ] Add a reversible production-safe `activity_log.created_at` index and measure the plan.
- [ ] Audit filter SQL receives one typed DTO, not raw arrays.
- [ ] Cache only scalar option catalogs for five minutes.
- [ ] Architecture coverage rejects untyped structured Query `build()` inputs.

## Implementation instructions

1. Run focused audit feature/browser tests, inspect schema, and capture MySQL `EXPLAIN` for unfiltered latest and representative date-range queries. Record current row count/plan without treating development data as a load benchmark.
2. Generate a migration with `php artisan make:migration add_created_at_index_to_activity_log_table --no-interaction`; add a named B-tree `created_at` index and reversible `down()`. Do not edit package create migrations, add speculative composites, disable retention, or move canonical data to Redis.
3. Add an authorized audit Form Request that validates nullable search, scalar event/causer/subject token arrays, `Y-m-d` dates, and `from <= to`, rejecting nested/invalid input. Move controller normalization into it.
4. Add immutable `ActivityLogFiltersData` with nullable search/dates and documented `array<int, string>` token lists. Construct it from validated data; give `ActivityLogQuery::build()` only this DTO. Keep System/Unspecified/No-subject token handling query-owned rather than leaking it into controllers.
5. Refactor the controller to authorize → DTO → Query → paginate → map while preserving Inertia props. Refactor the query to use typed filters, parameter binding, eager loading, and current ordering; remove validation-dead date fallbacks.
6. Wrap `filterOptions()` in five-minute `Cache::remember()` with a versioned application key. Cache only plain scalar arrays and preserve deterministic distinct catalogs. Do not cache pages; TTL is v1 invalidation unless product requirements prove immediate invalidation necessary.
7. After Task 25, extend `ArchTest.php` to inspect Query `build()` signatures for native parameter types and reject `array`, `mixed`, or absent structured-filter types. Exclude helper methods such as `filterOptions()`.
8. Test validation/ranges/special tokens/options/cache reuse-expiry, named index where portable, migration fresh/rollback, and repeat `EXPLAIN`. Do not implement S3 archival; existing 365-day cleanup stays authoritative pending measured volume/search requirements.
9. Run focused audit/architecture tests, Pint, PHP types/lint, frontend typecheck, full `composer test`, and `git diff --check`; move Task 26 after migration, plan, cache, and guardrail pass.

```markdown
## Summary
- describe the activity-log date index and measured query-plan change
- describe the typed audit request/DTO/query boundary and short-lived option cache
- describe the Query architecture guardrail and focused validation/cache coverage

## Verification
- list every focused and full quality-contract command and EXPLAIN/migration check actually run, with its result
```
