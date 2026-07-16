# Deployment And Backups Runbook

## Deployment decision

Southeast Code v1 deploys through [Laravel Forge](https://forge.laravel.com/docs/sites/deployments). Staging and
production are separate Forge sites with separate databases, Redis stores, domains, secrets, and deployment approvals.
Local development remains SQLite with database queues; only staging and production use MySQL, Redis, and Horizon.

The Southeast Code Operations Lead owns production readiness, incident coordination, backup verification, and access
reviews. The Technical Maintainer is the escalation owner for application, migration, queue, and release failures.
Operational alerts use a monitored mailbox configured through `HORIZON_NOTIFICATION_EMAIL`; internal lead mail remains
configured through the business setting.

Forge site and Supervisor logs are the v1 error-tracking source of truth. The `/up` health route feeds uptime
monitoring. Production must keep `APP_DEBUG=false`, and operators must preserve request IDs, timestamps, and safe
exception context without copying secrets or personal data into tickets. The Forge Sentry integration is optional until
the repository adds the Sentry SDK.

## Environment model

| Environment | Application | Database and cache | Queue and mail | Purpose |
| --- | --- | --- | --- | --- |
| Local | Herd or local PHP 8.5 | SQLite and database cache | Database queue and log mailer | Development and focused tests |
| Staging | Forge-managed staging site | Dedicated MySQL and Redis | Redis/Horizon and non-production mail | Release validation and restore rehearsals |
| Production | Forge-managed production site | Dedicated MySQL and Redis | Redis/Horizon and real mail | Live Southeast Code operations |

Staging and production must use `APP_ENV=staging` or `production`, `APP_DEBUG=false`, HTTPS, secure cookies, real
`APP_URL`, and environment-injected secrets. Never copy production data into staging without an approved, redacted
fixture process.

## Deployment contract

The Forge server/site provides PHP 8.5, MySQL, Redis, zero-downtime releases, Supervisor process supervision, scheduled
jobs, logs, and a deploy script. Inject secrets through Forge environment variables rather than committing environment
files.

For new Forge sites, enable zero-downtime deployments and keep the generated release macros in the deploy script.
Configure the Forge deploy script to install dependencies, build the Vite assets, and optimize the application:

```bash
$CREATE_RELEASE()
cd $FORGE_RELEASE_DIRECTORY
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan optimize
php artisan migrate --force
```

With zero-downtime deployments, run those commands after `$CREATE_RELEASE()` and before `$ACTIVATE_RELEASE()`, then
finish the script with:

```bash
$ACTIVATE_RELEASE()
$RESTART_QUEUES()
```

Forge activates the new release only after the deploy script completes successfully. For an existing site that cannot
use zero-downtime deployments, use the standard deployment strategy and manually restart Horizon through Supervisor
after the release.

After deployment, confirm `php artisan schedule:list`, `/up`, and the Horizon dashboard respond successfully. Check
public intake, login, CRM, admin, and jobs routes using the smoke checklist before declaring the release complete.

Keep `REDIS_QUEUE_RETRY_AFTER` greater than the 120-second Horizon worker timeout. Queued jobs should be written to
stable, version-tolerant contracts. A non-Forge fallback host must gracefully restart workers after every code release.

## Required processes

- Web server or Laravel-compatible application runtime
- `php artisan horizon` as a Forge Supervisor-managed background process
- A Forge site-level scheduled job invoking `php artisan schedule:run` every minute
- A queue failure alert recipient configured through `HORIZON_NOTIFICATION_EMAIL`

Forge owns process supervision and provides the server scheduler. A fallback host must run Horizon and the scheduler
under an equivalent process monitor and must follow the manual restart instructions in [queues-and-horizon.md](queues-and-horizon.md).

## Backup policy

Forge production must provide:

- Automated, encrypted MySQL backups at least daily to a configured Forge storage provider
- A separate storage account or provider for production backups, with credentials outside this repository
- A 365-day activity-audit retention window through `ACTIVITYLOG_CLEAN_AFTER_DAYS=365`
- Recovery credentials stored outside the application repository
- A documented restore owner and recovery objectives of RPO 24 hours and RTO 4 hours

Backups are not considered verified until a restore has been performed against an isolated staging database before
launch and at least monthly thereafter. After restoring, run migrations, verify seeded roles are present, check the
public intake and CRM records, and confirm Horizon can connect to Redis without processing the same production jobs.

## Failed jobs and incidents

1. Check the Admin > Jobs & queues page or `php artisan queue:failed`.
2. Inspect the failed job payload and exception without copying secrets into tickets or chat.
3. Confirm Redis, database, mail, and the relevant worker process are healthy.
4. Retry only after the underlying cause is fixed; use `jobs.manage` access.
5. Record recurring failures as a code or configuration change, not as a manual retry procedure.

For a stuck deployment, check the health endpoint, application logs, scheduler, Horizon status, and recent migration
before rolling back. Rollbacks must be compatible with jobs already serialized by the previous release.

## Launch ownership checklist

- Operations Lead confirms environment secrets, domains, mail routing, backup status, and alert delivery.
- Technical Maintainer confirms the release, migrations, asset build, Horizon, scheduler, and health route.
- Operations Lead records the release decision and any rollback in the deployment log.
- Both owners participate in the first isolated restore rehearsal before production launch.
