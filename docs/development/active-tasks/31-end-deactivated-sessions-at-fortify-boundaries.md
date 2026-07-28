# Task 31 — End Deactivated Sessions At Fortify Boundaries

Resolve F-04. Application and settings routes reject deactivated users, but Fortify's authenticated password-confirmation
and two-factor routes currently run with only Fortify's standard `web`/`auth` middleware, allowing an existing session
to retain access to sensitive security endpoints.

- [ ] Every authenticated Fortify password-confirmation and two-factor management endpoint runs
  `EnsureUserIsActive`.
- [ ] A deactivated existing session is logged out, invalidated, redirected to login, and cannot read or mutate
  two-factor state.
- [ ] Guest login/two-factor challenge behavior, active-user security flows, password confirmation, and throttling remain
  unchanged.
- [ ] A route/HTTP matrix covers every sensitive Fortify method and endpoint.

## Implementation instructions

1. Run the password-confirmation, two-factor, settings-security, launch-readiness, and admin-authorization feature tests.
   Capture `php artisan route:list --only-vendor` for Fortify routes and separate guest challenge routes from the
   authenticated password-confirmation, confirmation-status, QR code, secret key, recovery-code, enable, confirm, and
   disable routes.
2. Import `EnsureUserIsActive` in `config/fortify.php` and append the class to Fortify's configured route middleware
   after `web`. Reuse the current middleware behavior; do not duplicate active-user checks in Fortify controllers,
   replace Fortify routes, or change the authentication pipeline.
3. Confirm middleware order after clearing cached configuration. The `web` group must start the session before
   `EnsureUserIsActive` reads the authenticated user, and Fortify's route-specific `auth`, `password.confirm`, and
   throttle middleware must remain present.
4. Add a dataset-driven feature matrix for `password.confirm`, `password.confirm.store`, `password.confirmation`,
   `two-factor.confirm`, `two-factor.enable`, `two-factor.disable`, `two-factor.qr-code`,
   `two-factor.recovery-codes`, `two-factor.regenerate-recovery-codes`, and `two-factor.secret-key`. Give each case a
   fresh deactivated user/session, use the route's real HTTP method, and assert login redirect, inactive-account error,
   guest state, invalidated session, and unchanged encrypted two-factor fields.
5. Add a route-registry assertion that every authenticated Fortify route contains
   `EnsureUserIsActive::class`. Retain or extend active-user success/validation tests and the guest two-factor challenge
   tests so the global Fortify middleware change cannot accidentally block login completion.
6. Run the focused auth/security tests with configuration both cleared and cached, then
   `vendor/bin/pint --dirty --format agent`, both PHPStan levels, the full Herd coverage contract, and
   `git diff --check`. Restore a cleared configuration cache when verification finishes.
7. Once completed, provide the user with a code block containing a PR summary (see below) for these changes.

```markdown
Implement Task 31: End Deactivated Sessions At Fortify Boundaries

## Summary
- describe the shared active-account middleware added to Fortify routes
- describe the preserved guest, password-confirmation, two-factor, and throttle behavior
- describe the deactivated-session endpoint and mutation matrix

## Verification
- list every focused and full quality-contract command actually run, with its result
```
