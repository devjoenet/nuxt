import DefaultLayout from "@/layouts/Default.vue";
import type { Page } from "@inertiajs/core";
import type { Component } from "vue";

const pagesWithoutWorkspaceLayout = new Set(["ErrorPage", "Privacy", "Welcome"]);

export function resolveAppLayout(name: string, _page: Page): Component | null {
  if (pagesWithoutWorkspaceLayout.has(name) || name.startsWith("auth/")) {
    return null;
  }

  return DefaultLayout;
}
