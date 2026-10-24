---
name: user-management-development
description: Integrate user invitations, role administration, access controls, and administrative 2FA reset through southeastcode/user-management.
license: MIT
metadata:
  author: Joey Harris
---

# User Management

## Primary goal

Install and use the optional `southeastcode/user-management` module without duplicating foundation authentication, schemas, or administrator provisioning.

## Workflow

1. For installation, dependency authentication, module preparation, and build setup, read [installation](references/installation.md).
2. Keep the configured user model, user/Permission/Activitylog migrations, Fortify/self-service authentication and signed verification in the foundation. The configured Eloquent model implements `WorkspaceUser`, `MustVerifyEmail`, `CanResetPassword` and the documented role/notification/2FA capabilities and invitation fields.
3. Use typed package DTOs and explicit actors when invoking state-changing Actions. Invitation acceptance has one owner: the package's listener on Laravel `PasswordReset`. Keep the foundation password reset controller emitting that event; do not call a second invitation action.
4. Frontend dependencies use `@southeastcode/host`; routes use `@southeastcode/transport/users/admin/users` and `/roles`. Payload types are exported from `@southeastcode/users/public`. Generate transports in the consumer; never ship snapshots.
5. Verify affected authorization, invitation expiry/acceptance, queued notifications, role protection, administrative two-factor reset, and user/role Audit visibility in the consumer. Use disposable test data and report unverified behavior.

## References

- `README.md` for root repositories, configured-user capabilities, ownership and verification.
- `config/user-management.php` for optional publishable configuration (`user-management-config`). Disable contributions before boot with `user-management.enabled=false`; retain persisted grants/data.
- `src/Actions` and `src/Data` for explicit-actor typed operations; `routes/user-management.php` for administrative endpoints.

## Examples

After installing a compatible revision, run the foundation permission seeder, then `corepack yarn modules:prepare` and the affected frontend gates. Run the consumer's authorization and browser regressions.

## Anti-patterns

Do not add a second users table, copy authentication routes, depend on `App` classes, install frontend dependencies under vendor, remove another module's grants, or expose unfiltered audit subjects. Never commit local paths/credentials or claim hosted/staging acceptance from local checks.
