import type { ArgonGalleryFamily } from "@/types/argonGallery";

export const galleryFamilies: ArgonGalleryFamily[] = ["account", "applications", "authentication", "dashboards", "ecommerce", "general", "projects", "team"];

export function galleryLabel(value: string): string {
  return value
    .split("-")
    .map((word) => `${word.charAt(0).toUpperCase()}${word.slice(1)}`)
    .join(" ");
}
