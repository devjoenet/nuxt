export function getNestedFieldError(errors: Record<string, string>, field: string): string | undefined {
  return errors[field] ?? Object.entries(errors).find(([key]) => key.startsWith(`${field}.`))?.[1];
}
