# Task 20 — Make The Organization Owner Filter Clearable

This is a frontend-only repair for the organizations-index owner filter. Do not change organization assignment, CRM ownership policy, server visibility rules, query code, schema, dependencies, or the shared `FormSelect` API unless current evidence disproves the recorded cause.

- [ ] An Admin or Super Admin can clear a selected owner and return to `All owners`.
- [ ] Clearing omits `owner` while preserving search and uses no empty-valued select item.
- [ ] Browser coverage proves select, clear, URL state, and narrow-screen behavior.

## Implementation instructions

1. Reproduce as an Admin with two organizations owned by different active users. Run `php artisan test --compact tests/Feature/Crm/EntityWorkspaceTest.php` first and keep the change scoped to `resources/js/pages/Crm/Organizations/Index.vue`.
2. Keep `owner` as the existing string ref and real IDs as the only `ownerOptions`. Add `const clearOwnerFilter = (): void => { owner.value = ""; };`. Do not add `{ label: "All owners", value: "" }`: Reka reserves the empty value for clearing. Preserve the debounced watcher and its `owner: owner.value || undefined` payload, generated Wayfinder URL, search value, `preserveState`, and `replace` behavior.
3. Give the select `id="organization-owner-filter"`. Beside it, render a page-local, keyboard-reachable `UButton` only when `owner` is set, with `aria-label="Clear owner filter"`, neutral ghost styling, `type="button"`, and `@click="clearOwnerFilter"`. Keep the group full-width on small screens; do not nest the button in the select trigger.
4. Preserve the optional server-side `owner` contract. Extend the feature test only if the browser case exposes a missing assertion: selected owner narrows results and omitted `owner` restores both results.
5. Add `tests/Browser/CrmOrganizationFilterTest.php` using existing Pest browser conventions and backend fixtures. Authenticate an Admin, select an owner through `#organization-owner-filter`, assert the scoped rows and URL, clear with the accessible button, then assert both rows, no `owner` parameter, `All owners`, no console errors/logs, and correct behavior at a narrow viewport. Use unique fixture names and visible/accessibility selectors; no browser-created fixtures or arbitrary sleeps.
6. Run the focused feature/browser tests, then (as applicable) Pint, `npm run typecheck`, `npm run lint:check`, `npm run format:check`, `npm run build`, full `composer test`, and `git diff --check`. Report commands actually run.
7. On completion, move the task to the completed roadmap and use the authorized user-testing workflow to prepend `FIXED VIA TASK-20` to the organization-index note, preserving its feedback and `tested_at`/`roadmap_task_at`, leaving `approved_at` null, and verifying the card remains `Needs Approval`.

```markdown
## Summary
- describe the accessible organization owner-filter reset and preserved search state
- describe the Reka-safe empty-state handling and unchanged ownership/query boundaries
- describe the focused browser regression and user-testing handoff

## Verification
- list every focused and full quality-contract command actually run, with its result
```
