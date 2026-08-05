const dateFormatter = new Intl.DateTimeFormat("en-US", { dateStyle: "medium" });
const dateTimeFormatter = new Intl.DateTimeFormat("en-US", { dateStyle: "medium", timeStyle: "short" });

export function formatDate(value: string | null | undefined, fallback = "Not recorded"): string {
  return value ? dateFormatter.format(new Date(value)) : fallback;
}

export function formatDateTime(value: string | null | undefined, fallback = "Not recorded"): string {
  return value ? dateTimeFormatter.format(new Date(value)) : fallback;
}
