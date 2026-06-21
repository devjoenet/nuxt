# Technical To-Do: Phase 5 — Jobs, Queues, Horizon, And Operations

## Goals

Make background processing intentional, observable, permission-protected, and aligned across environments.

## Execution Baseline

- Local/simple development defaults use database queues so the app can run without Redis.
- Testing should fake queues, notifications, and mail unless the test is explicitly proving queue persistence,
  scheduling, or Horizon authorization.
- Staging and production use Redis queues with Horizon.
- Horizon access is authorized through roles/permissions, not an email allow-list.
- Queue names should be stable and capability-based: `default`, `mail`, `crm`, `notifications`, `reminders`, `imports`,
  `exports`, `reports`, and `maintenance`.
- Horizon supervisor timeout must be greater than any job-level timeout, and queue `retry_after` must be greater than
  the
  Horizon supervisor timeout to avoid duplicate processing.
- Jobs that send notifications or reminders must be idempotent enough to tolerate retries.

## Queue Configuration

- Apply the queue backend defaults for local, testing, staging, and production.
- Update `.env.example`, config, README, and deployment notes to match the chosen queue strategy.
- Configure Redis/Horizon for staging and production.
- Configure database queues for local/simple environments.
- Define queue names for default, mail, CRM notifications, reminders, imports, exports, reports, and maintenance.
- Define worker timeout, retry, backoff, memory, and process counts.

## Horizon And Horizon UI

- Protect Horizon and Horizon UI with role/permission checks.
- Replace empty email allow-lists with policy/permission-backed access.
- Configure Horizon supervisors, balancing, queues, process counts, and wait thresholds.
- Add Horizon tags for CRM, notifications, reminders, imports, exports, and operational jobs.
- Schedule Horizon snapshots.
- Add tests for Horizon authorization expectations.

## Jobs And Notifications

- Create queued jobs for internal lead notifications.
- Defer visitor intake confirmation email jobs unless DP-08 enables visitor confirmations.
- Create queued jobs for follow-up reminders.
- Defer daily/weekly activity digest jobs unless DP-08 enables digests.
- Create cleanup jobs only for stale temporary records or old notifications introduced by the implementation; otherwise
  document that no cleanup job is required for v1.
- Add notification classes for failed-job alerts and long-wait alerts after recipients/channels are resolved.
- Add tags and structured logging to jobs.
- Add idempotency safeguards for notification/reminder jobs.

## Jobs Admin Area

- Add admin links to failed jobs, pending jobs, batches, job search, and Horizon views.
- Build permission-aware frontend pages or wrappers around existing Horizon UI pages.
- Add operational empty states and troubleshooting copy.
- Add action flows for retrying, forgetting, or inspecting failed jobs where supported.
- Add tests for job admin access and protected actions.

## Scheduling And Runbooks

- Register scheduler tasks for Horizon snapshots, reminder scans, digests, and cleanup.
- Document scheduler requirements for deployment.
- Document worker process requirements.
- Add a queue troubleshooting runbook after the real behavior is implemented.
- Add verification commands for queue workers, failed jobs, scheduler, and Horizon access.

## Acceptance Criteria

- `.env.example`, queue config, Horizon config, README/deployment notes, and phase docs agree on local, testing,
  staging,
  and production queue drivers.
- Horizon and Horizon UI are inaccessible to users without `jobs.view` or stronger permission and manageable actions
  require `jobs.manage`.
- Horizon supervisors define queues, process counts, balancing, memory, backoff, retry, and timeout values appropriate
  for the selected environments.
- Horizon snapshots are scheduled.
- CRM lead notifications, visitor confirmations if enabled, reminders, digests if enabled, and cleanup jobs have queue
  names, tags, retries/backoff, and idempotency safeguards.
- Failed-job and long-wait notifications have clear recipients/channels once DP-08 and DP-10 are resolved.
- Jobs admin pages or links expose failed jobs, pending jobs, batches, search, Horizon views, and supported retry/forget
  actions without bypassing permissions.
- Runbook notes are written only after the real commands, routes, permissions, and scheduler behavior exist.

## Verification

- User-run verification after this phase:
  - `composer test`
  - `composer test:types`
  - `composer test:lint`
  - `npm run build`
  - targeted queue/Horizon authorization tests
