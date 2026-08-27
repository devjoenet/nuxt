export function formatPhoneNumber(value: string): string {
  const digits = value.replace(/\D/g, "");
  const normalized = digits.length === 11 && digits.startsWith("1") ? digits.slice(1) : digits.slice(0, 10);

  if (normalized.length <= 3) {
    return normalized;
  }

  if (normalized.length <= 6) {
    return `${normalized.slice(0, 3)}-${normalized.slice(3)}`;
  }

  return `${normalized.slice(0, 3)}-${normalized.slice(3, 6)}-${normalized.slice(6)}`;
}
