# Follow-up reminders

CRM schedules crm:dispatch-follow-up-reminders once every fifteen minutes with overlap/single-server protection. Do not schedule it again. Jobs use FollowUps\Jobs\SendFollowUpTaskReminder and the mail queue. Configure crm.reminders.recipient or bind FollowUps\Contracts\ReminderRecipient to a host resolver. Delivery is best-effort: failures/missing recipients remain retryable that day; a successful local sent timestamp suppresses another same-day send. Provider acceptance followed by a crash before persistence can duplicate mail. See README for the full state/retry contract; locks and unique jobs are not provider idempotency.

Consult the package README section Reminder delivery guarantee for retry, lock, and crash-window details before changing queue or delivery behavior.
