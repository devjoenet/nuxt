import ApplicationLayout from "@/layouts/ApplicationLayout.vue";
import AuthLayout from "@/layouts/AuthLayout.vue";
import PublicLayout from "@/layouts/PublicLayout.vue";
import SettingsLayout from "@/layouts/SettingsLayout.vue";
import type { Page } from "@inertiajs/core";
import type { Component } from "vue";

const publicPages = new Set(["Privacy", "Welcome"]);

export function resolveAppLayout(name: string, _page: Page): Component | Component[] | null {
  if (name === "ErrorPage") {
    return null;
  }

  if (publicPages.has(name)) {
    return PublicLayout;
  }

  if (name.startsWith("Auth/")) {
    return AuthLayout;
  }

  if (name.startsWith("Settings/")) {
    return [ApplicationLayout, SettingsLayout];
  }

  return ApplicationLayout;
}
