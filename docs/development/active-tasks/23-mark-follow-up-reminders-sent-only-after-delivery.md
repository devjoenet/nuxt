# Task 23 — Mark Follow-Up Reminders Sent Only After Delivery

Resolve F-03 by making the reminder timestamp reflect successful mail-channel delivery, not queue dispatch.

- [ ] Mail delivery owns the `sent_at` transition.
- [ ] Failed mail leaves the task eligible for retry.
- [ ] Notification queue behavior remains explicit and testable.

## Implementation instructions

1. Run the focused follow-up reminder tests and inspect the current job/notification ownership. Confirm which queue consumes mail; do not hide queue selection or weaken retry behavior.
2. Keep `SendFollowUpTaskReminder` responsible for selecting due work and dispatching/performing the delivery boundary. Use the mail queue and call `notifyNow` (or the repository's equivalent synchronous channel delivery) so the task timestamp can be set only after delivery returns successfully.
3. Move/remove `ShouldQueue` from the notification if it creates a second dispatch that causes early stamping. Stamp `sent_at` only after success; let failures bubble so retries occur and no timestamp is written.
4. Add focused tests with a real failing `MailChannel` path plus a success case. Assert success stamps once, failure leaves `sent_at` null, retries remain possible, and duplicate invocation is idempotent or explicitly protected.
5. Run focused tests, Pint if PHP changed, the relevant queue/mail contract, full `composer test`, and `git diff --check`. Do not broaden this into a notification-system rewrite.

```markdown
## Summary
- describe post-delivery reminder timestamping and preserved retry behavior
- describe the explicit mail queue/notification boundary
- describe successful and failing mail-channel coverage

## Verification
- list every command actually run, with its result
```
