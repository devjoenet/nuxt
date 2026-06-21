# Technical To-Do: Phase 5 — Jobs, Queues, Horizon, And Operations

## Goals

Make background processing intentional, observable, permission-protected, and aligned across environments.

## Queue Configuration

- Choose the queue backend for local, testing, staging, and production.
- Update `.env.example`, config, README, and deployment notes to match the chosen queue strategy.
- Configure Redis/Horizon for production if selected.
- Configure database queues for local/simple environments if retained.
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
- Create queued jobs for visitor intake confirmation emails.
- Create queued jobs for follow-up reminders.
- Create queued jobs for daily/weekly activity digests if in scope.
- Create cleanup jobs for stale temporary records or old notifications if needed.
- Add notification classes for failed-job alerts and long-wait alerts if selected.
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
