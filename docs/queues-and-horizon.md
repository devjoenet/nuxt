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

On Laravel Forge, configure a site-level scheduled job for `php artisan schedule:run` every minute and configure
`php artisan horizon` as a Supervisor-managed background process. Forge restarts the process if it exits:

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
7. After a Forge zero-downtime deployment, include Forge's `$RESTART_QUEUES()` macro after `$ACTIVATE_RELEASE()` so
   Horizon restarts on the new release. On a manual deployment, run `php artisan horizon:terminate` so Supervisor
   restarts workers with the new code.

Reminder failure application logs include the task UUID, reminder date, a stable `correlation_id`, queued job ID when
available, and exception class. They omit exception messages and exception objects. Use the job ID in the restricted
Horizon failed-jobs view, or correlate the task UUID/reminder date with the stored failed-job payload, to inspect the
original exception. Original failed-job details and operational alert mail may contain personal data or credentials;
only authorized operators should access them, and they must not be copied into general logs or roadmap notes.
