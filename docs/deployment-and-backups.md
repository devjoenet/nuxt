# Deployment And Backups Runbook

## Deployment contract

Production should provide PHP 8.5, MySQL, Redis, a process monitor, a scheduler, and a build step for Vite assets.
Inject secrets through the deployment platform rather than committing environment files.

1. Build frontend assets with `npm ci` and `npm run build`.
2. Put the application in maintenance mode if the platform does not provide atomic releases.
3. Run `php artisan migrate --force` against the release database.
4. Run `php artisan optimize` after configuration and route changes.
5. Run `php artisan horizon:terminate` so the process monitor starts workers with the new release.
6. Confirm `php artisan schedule:list`, the health endpoint, and the Horizon dashboard respond successfully.
7. Exit maintenance mode and check the public intake, login, CRM, admin, and jobs routes.

Keep `REDIS_QUEUE_RETRY_AFTER` greater than the 120-second Horizon worker timeout. Restart workers after every code
release; queued jobs should be written to stable, version-tolerant contracts.

## Required processes

- Web server or Laravel-compatible application runtime
- `php artisan horizon` under a process monitor
- `php artisan schedule:work` or a scheduler invoking `php artisan schedule:run` every minute
- A queue failure alert recipient configured through `HORIZON_NOTIFICATION_EMAIL`

## Backup policy

The deployment platform must provide:

- Automated, encrypted MySQL backups at least daily
- A retention window appropriate for the business and legal requirements
- A separate backup location or provider account
- Recovery credentials stored outside the application repository
- A documented restore owner and recovery time objective

Backups are not considered verified until a restore has been performed against an isolated database. After restoring,
run migrations, verify seeded roles are present, check the public intake and CRM records, and confirm Horizon can
connect to Redis without processing the same production jobs.

## Failed jobs and incidents

1. Check the Admin > Jobs & queues page or `php artisan queue:failed`.
2. Inspect the failed job payload and exception without copying secrets into tickets or chat.
3. Confirm Redis, database, mail, and the relevant worker process are healthy.
4. Retry only after the underlying cause is fixed; use `jobs.manage` access.
5. Record recurring failures as a code or configuration change, not as a manual retry procedure.

For a stuck deployment, check the health endpoint, application logs, scheduler, Horizon status, and recent migration
before rolling back. Rollbacks must be compatible with jobs already serialized by the previous release.
