# Southeast Code Operating Workspace

Southeast Code is a Laravel 13 and Inertia/Vue workspace for customer intake, CRM follow-up, role-protected admin
operations, queues, and audit visibility. It is built for a single Southeast Code workspace in v1.

## Stack

- PHP 8.5 and Laravel 13
- Inertia.js 3 with Vue 3 and TypeScript
- Nuxt UI 4 and Tailwind CSS 4
- Laravel Fortify for authentication and two-factor authentication
- Laravel Wayfinder for typed frontend routes
- MySQL for staging/production, SQLite for lightweight local work
- Database queues locally; Redis and Horizon in staging/production
- Pest, Larastan, Pint, Rector, ESLint, Prettier, and Vite for quality checks
- Pest browser testing with Playwright Chromium for critical-flow smoke coverage
- Spatie Permission and Activitylog for authorization and audit history

## Local setup

Requirements: PHP 8.5, Composer, Node.js/npm, and either SQLite or MySQL. Laravel Herd can serve the project at the
configured local site URL.

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install
npx playwright install chromium
npm run build
```

Use `composer run dev` for the normal local development processes. Local defaults use the database queue, database
cache, and log mailer, so Redis and an SMTP provider are not required for the first run.

## Application areas

- Public marketing and privacy pages, including the rate-limited contact intake form
- Authenticated workspace dashboard and CRM lead workflows
- Admin users, roles, business settings, audit log, and queue operations
- Fortify login, email verification, password reset, password confirmation, and 2FA
- Horizon at `/horizon`, protected by `jobs.view` and `jobs.manage`

The client portal, proposals, contracts, invoices, campaigns, knowledge base, and CRM import/export remain later
modules. They are intentionally not exposed in navigation.

## Architecture

HTTP controllers coordinate requests and responses. Form Requests own validation, Action classes own business side
effects, Eloquent models own persistence and relationships, Policies/Gates own authorization, Resources and query
classes shape page data, and Pest feature tests prove workflows and access boundaries. Vue pages live in
`resources/js/pages`, shared UI lives in `resources/js/components`, and frontend route calls use generated Wayfinder
imports.

## Queues and deployment

Local and test environments use `QUEUE_CONNECTION=database`. Staging and production should use Redis with Horizon,
workers for the configured queues, and a scheduler process. The ratified production model is a Laravel Forge-managed
server, with separate staging and production sites. [Laravel Herd's Forge integration](https://herd.laravel.com/docs/macos/integrations/laravel-forge)
can map the local site to its Forge site for deploys and remote access. Keep the queue retry window longer than the
Horizon worker timeout: the current defaults are 180 seconds and 120 seconds.

Configure `LEAD_NOTIFICATION_EMAIL` and `HORIZON_NOTIFICATION_EMAIL` in deployed environments. Set real mail, MySQL,
Redis, application URL, HTTPS cookie, and backup settings before production use.

Required long-running processes in staging/production:

```bash
php artisan horizon
php artisan schedule:run
```

See [docs/queues-and-horizon.md](docs/queues-and-horizon.md) and [docs/deployment-and-backups.md](docs/deployment-and-backups.md)
for worker, failed-job, scheduler, deployment, backup, ownership, and incident expectations. The [admin and user guide](docs/admin-and-user-guide.md)
covers the CRM lifecycle, access management, roles, jobs, and audit review. Architecture decisions are recorded in
[docs/development/adr](docs/development/adr/).

## Verification

Run the focused checks while developing and the complete quality contract before merging:

```bash
php artisan test --compact
composer test:types
composer test:lint
npm run build
```

Useful targeted checks include:

```bash
php artisan test --compact tests/Feature/PublicIntakeTest.php
php artisan test --compact tests/Feature/Crm/LeadWorkspaceTest.php
php artisan test --compact tests/Feature/AdminAuthorizationTest.php
php artisan test --compact tests/Feature/JobsOperationsTest.php
```

`composer test` also runs the repository's type-coverage, feature/unit coverage, browser smoke suite, formatting,
Rector, and static analysis checks. Run `composer test:browser` when iterating on browser coverage alone. Frontend
checks are available through `npm run lint:check`, `npm run format:check`, and `npm run typecheck`.

## Project conventions

- Keep authorization server-side; frontend permission props only control visibility.
- Use named routes and Wayfinder-generated functions in Vue.
- Use Pest for new tests and factories for test data.
- Do not add client-facing navigation for deferred modules.
- Do not log passwords, tokens, recovery codes, 2FA secrets, or other authentication material.
