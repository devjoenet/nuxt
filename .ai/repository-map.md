# Repository map

## Role

Host Laravel 13/Inertia workspace. Owns application-specific workflows, composition, deployment configuration, and integration of first-party modules.

## Start here

- `composer.json`: PHP dependencies and delivery scripts
- `package.json`: frontend scripts and package versions
- `app/Providers/ModuleServiceProvider.php`: module composition
- `resources/js/app.ts`: client entrypoint
- `routes/web.php`: host routes
- `tests/Feature/`: application and architecture contracts
- `.ai/rules/index.md`: path-scoped rules

## Source boundaries

- Reusable module behavior belongs in `/Users/joey/code/packages`, not `vendor/`.
- Host integration belongs in `app/`, `resources/`, `routes/`, and host tests.
- Treat `vendor/`, `node_modules/`, `bootstrap/cache/`, `storage/`, generated Wayfinder output, and build assets as derived or external.
- Use disposable consumers/databases for extraction validation; preserve the normal `nuxt.test` application and Development database.

## Verification

Read the verification-and-delivery-checks skill. Resolve current commands and aggregate overlap from composer.json, package.json, and .github/workflows/tests.yml.

## Related repositories

First-party packages live in `/Users/joey/code/packages`. Changes crossing the host/package boundary require package validation before host dependency normalization.
