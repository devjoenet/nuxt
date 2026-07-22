# ADR 0002: Use Business Routing And Redacted Audit Retention

- Status: Accepted
- Date: 2026-07-15
- Decision points: DP-08 and DP-09

## Context

Lead notifications, follow-up reminders, failed-job alerts, long-wait alerts, and audit records need predictable
recipients, queue behavior, retention, and privacy boundaries before production operations begin.

## Decision

Internal lead alerts and follow-up reminders use the current `BusinessSetting::notification_email` on the mail queue.
Horizon failed-job and long-wait alerts use `HORIZON_NOTIFICATION_EMAIL`. Visitor confirmations and scheduling links
remain deferred.

Audit records are retained for 365 days and cleaned daily at 02:15. Audit search covers safe descriptions, log/event
names, causers, subjects, and date ranges. Passwords, tokens, recovery codes, two-factor material, note bodies, contact
email/phone values, notification recipients, and exception messages are not stored in audit changes.

## Consequences

- Business settings control internal lead routing without a code release.
- Mail and Horizon remain observable through separate operational destinations.
- The audit log is useful for accountability without becoming a copy of sensitive application data.
- Visitor confirmation, calendar scheduling, and longer audit retention require a new decision and implementation.
