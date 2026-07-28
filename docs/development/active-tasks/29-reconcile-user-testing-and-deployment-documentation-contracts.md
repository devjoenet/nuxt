# Task 29 — Reconcile User-Testing And Deployment Documentation Contracts

Resolve F-10. The merged Task 22, 23, 24, and 27 records were moved to the completed roadmap while this audit was
planned, but the user-testing status map still labels completed work `In Development`, the deployment runbook still
requires the removed Larascan command, and no automated contract prevents those documentation failures from returning.

- [ ] Completed roadmap mappings render as `Needs verification`, not `In Development`, without changing persisted
  review, roadmap, or approval timestamps.
- [ ] Deployment instructions contain only current release checks and no removed Larascan command or environment key.
- [ ] A focused Pest contract proves every active-task link resolves and every documented Artisan command exists.
- [ ] The active roadmap, completed roadmap, runtime roadmap page, and user-testing expectations agree.

## Implementation instructions

1. Run the focused roadmap/user-testing tests, inspect `config/user-testing.php`, and inventory every
   `active-tasks/*.md` link plus every `php artisan ...` command in deployment documentation. Confirm Tasks 22, 23,
   24, and 27 remain recorded in `completed-roadmap.md`; do not renumber any existing or newly added task.
2. Populate `config('user-testing.roadmap_task_statuses')` for every mapped task that is already complete with
   `Needs verification`. Keep `roadmap_tasks` stable, preserve the existing `Needs Approval` note precedence, and do
   not mutate `tested_at`, `tested_by`, `roadmap_task_at`, `approved_at`, or notes as part of this repair.
3. Remove `LARASCAN_PROBE_URL` and `larascan:probe` from `docs/deployment-and-backups.md`. Replace them with a concise
   pre-deployment requirement for current CI/release evidence, including `composer audit --locked` and
   `npm audit --omit=dev --audit-level=high`; do not claim the full development npm audit is clean before Task 36
   resolves or explicitly tracks its upstream advisory.
4. Generate `DocumentationContractTest` with `php artisan make:test --pest DocumentationContractTest --no-interaction`.
   Keep the test filesystem-only except for Laravel's command registry: resolve Markdown links relative to their source,
   fail with the missing path, extract documented `php artisan <command>` invocations from maintained runbooks, and
   compare command names with `Artisan::all()`. Ignore shell variables and Forge macros rather than attempting to
   execute deployment commands.
5. Update `UserTestingPageTest` to assert completed mapped pages render `Needs verification`; retain separate coverage
   for `Needs Approval`, `Reviewed`, and genuinely active task statuses. Add a roadmap-rendering assertion that includes
   Task 29 and excludes the removed active headings for Tasks 22, 23, 24, and 27.
6. Run the documentation contract and user-testing feature tests, `vendor/bin/pint --dirty --format agent`,
   `composer test:types`, `composer test:lint`, the full coverage contract through Herd, and `git diff --check`. Verify
   the runtime roadmap response contains no missing local links.
7. Once completed, provide the user with a code block containing a PR summary (see below) for these changes.

```markdown
Implement Task 29: Reconcile User-Testing And Deployment Documentation Contracts

## Summary
- describe the completed-task status and roadmap reconciliation
- describe the removal of obsolete Larascan deployment instructions and the current release evidence
- describe the active-link and Artisan-command documentation guardrail

## Verification
- list every focused and full quality-contract command actually run, with its result
```
