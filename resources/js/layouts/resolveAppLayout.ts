import DefaultLayout from "@/layouts/Default.vue";
import type { Page } from "@inertiajs/core";
import type { Component } from "vue";

const pagesWithoutWorkspaceLayout = new Set(["ErrorPage", "Privacy", "Welcome"]);
const pageEntrySuffix = "/Page";

export function resolveAppLayout(name: string, _page: Page): Component | null {
  const logicalPageName = name.endsWith(pageEntrySuffix) ? name.slice(0, -pageEntrySuffix.length) : name;

  if (pagesWithoutWorkspaceLayout.has(logicalPageName) || logicalPageName.startsWith("Auth/")) {
    return null;
  }

  return DefaultLayout;
}
