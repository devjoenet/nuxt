# Task 36 — Enforce Dependency Audit Policy

Resolve F-09 without using npm's forced downgrade. Production npm dependencies and Composer are currently clean, but
the full npm audit reports seven high findings through the latest Blade formatter's transitive
`brace-expansion@2.1.3` chain. Block deployable dependency advisories now and either remove the development chain safely
or keep it explicitly visible until upstream supplies a compatible fix.

- [ ] CI fails on Composer advisories and high-or-critical production npm advisories before expensive build/test work.
- [ ] The full development npm audit either passes after a compatible formatter update/replacement or remains an
  explicit non-blocking CI check with the upstream advisory visible.
- [ ] No forced package downgrade, incompatible major override, blanket audit suppression, or production omission is
  used to create a false green result.
- [ ] Lockfile, Blade formatting, frontend formatting, clean install, and the complete quality contract remain stable.

## Implementation instructions

1. From a clean install, record `composer audit --locked`, `npm audit --omit=dev --audit-level=high`,
   `npm audit --audit-level=high`, and `npm explain` for the formatter → `blade-formatter` → `js-beautify` /
   `editorconfig` → `minimatch` → `brace-expansion` chain. Confirm advisory `GHSA-mh99-v99m-4gvg` and current
   fix availability instead of relying on the audit snapshot in this task.
2. First try a normal update of `@shufo/prettier-plugin-blade` and its lockfile-resolved chain. If a compatible release
   removes the advisory, keep it and prove Blade formatting output is unchanged. If not, evaluate removing/replacing
   the formatter only if the existing Blade files remain deterministically covered by the repository formatting
   contract. Do not force npm's suggested downgrade or pin `brace-expansion@5` beneath consumers that request older,
   incompatible APIs.
3. Add concise package scripts for the production and full development npm audits if that improves local/CI parity.
   Keep production dependencies included in the normal install; `--omit=dev` applies only to the audit view, not to
   application installation or build inputs.
4. In `.github/workflows/tests.yml`, run `composer audit --locked --no-interaction` after Composer install and
   `npm audit --omit=dev --audit-level=high` after `npm ci`, before Playwright installation/build/full tests. Keep these
   blocking. Preserve Dependabot's weekly Composer/npm checks.
5. If the development chain is still upstream-blocked, add a separately named full `npm audit --audit-level=high` CI
   step with `continue-on-error: true` and the advisory identifier in the step name or nearby explanation. Do not
   filter arbitrary packages or mark the blocking production audit non-blocking. Remove `continue-on-error` as soon as
   a compatible formatter chain is available.
6. Verify `npm ci`, both npm audit modes, Composer audit, Blade/Prettier formatting, frontend
   typecheck/lint/format/build, `composer test:lint`, both PHPStan levels, the full Herd coverage/browser contracts, and
   `git diff --check`. Report an expected development-audit warning separately from passing production gates.
7. Once completed, provide the user with a code block containing a PR summary (see below) for these changes.

```markdown
Implement Task 36: Enforce Dependency Audit Policy

## Summary
- describe the Composer and production npm audit gates added to CI
- describe the compatible formatter-chain resolution or explicit upstream development-audit tracking
- describe preserved clean-install, formatting, build, and dependency-update behavior

## Verification
- list every audit, install, formatting, build, and full quality-contract command actually run, with its result
```
