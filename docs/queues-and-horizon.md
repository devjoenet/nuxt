# Queues And Horizon Runbook

## Environment contract

- Local and test environments default to the `database` queue connection.
- Staging and production use `redis` with Laravel Horizon.
- `DB_QUEUE_RETRY_AFTER` and `REDIS_QUEUE_RETRY_AFTER` must remain greater than the Horizon supervisor timeout.
- The current supervisor timeout is 120 seconds and the default retry window is 180 seconds.
- Configure `HORIZON_NOTIFICATION_EMAIL` for long-wait and failed-job alerts.
- Internal lead alerts and follow-up reminders use the admin-configured business notification email. Visitor
  confirmations and scheduling links are deferred.

## Queue topology

- `default`: general application work.
- `mail`: lead alerts and operational email.
- `crm`: CRM workflows and future imports/exports.
- `notifications`: notification fan-out.
- `reminders`: follow-up reminder jobs.
- `imports`, `exports`, `reports`, and `maintenance`: reserved capability-based queues for future operational jobs.

The default Horizon supervisor handles general CRM and operational queues. The notification supervisor handles mail,
notifications, and reminders separately so alert traffic does not wait behind general work.

## Required processes

Keep the scheduler running every minute and keep Horizon under a process monitor in staging/production:

```bash
php artisan schedule:run
php artisan horizon
```

The scheduler registers a Horizon metrics snapshot every five minutes and dispatches due follow-up reminders every
fifteen minutes, then cleans audit records older than 365 days daily. Use the schedule worker locally when testing
those schedules.

## Access model

- `jobs.view` grants access to the admin operations page and read-only Horizon routes.
- `jobs.manage` is required for non-safe Horizon requests, including retry and supervisor actions.
- Official Horizon is the only active queue UI. The unused third-party Horizon UI package remains undiscovered so it
  cannot register an unprotected route tree.
- Successful Horizon mutations and failed queue jobs appear in the operations audit log with safe metadata only.

## Troubleshooting

1. Check the queue connection with `php artisan config:show queue.default`.
2. Check worker health with `php artisan horizon:status`.
3. Inspect failures with `php artisan queue:failed` or the Admin > Jobs & queues failed-jobs link.
4. Retry a supported failure from the Horizon failed-jobs view with a `jobs.manage` account.
5. If metrics are blank, confirm the scheduler is running and `horizon:snapshot` is registered.
6. If jobs are processed twice, compare the job timeout, Horizon timeout, and queue `retry_after`; they must be
   ordered from shortest to longest.
7. After deployment, run `php artisan horizon:terminate` so the process monitor restarts workers with the new code.
