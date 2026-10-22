import { moduleLayouts } from "../../../build/modules/pages";
import ApplicationLayout from "@/layouts/ApplicationLayout.vue";
import AuthLayout from "@/layouts/AuthLayout.vue";
import PublicLayout from "@/layouts/PublicLayout.vue";
import SettingsLayout from "@/layouts/SettingsLayout.vue";
import type { Page } from "@inertiajs/core";
import type { Component } from "vue";

const publicPages = new Set(["Privacy", "Welcome"]);

export function resolveAppLayout(name: string, _page: Page): Component | Component[] | null {
  if (name.includes("::")) {
    const layout = moduleLayouts[name] ?? moduleLayouts[name.split("::")[0]];
    if (!layout) throw new Error(`Unknown module page namespace: ${name}`);
    if (layout === "none") return null;
    if (layout === "public") return PublicLayout;
    if (layout === "auth") return AuthLayout;
    if (layout === "settings") return [ApplicationLayout, SettingsLayout];
    return ApplicationLayout;
  }

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
