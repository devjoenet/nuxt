export function isWebUrl(value: string | null | undefined): boolean {
  return value?.startsWith("https://") === true || value?.startsWith("http://") === true;
}
