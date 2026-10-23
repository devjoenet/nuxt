import { defineAsyncComponent, type Component } from "vue";

// In-place registrations move with their module during package extraction.
export const workspaceDashboardWidgets: Record<string, Component> = {
  crm: defineAsyncComponent(() => import("@southeastcode/crm/public").then((module) => module.CrmWorkspaceDashboard)),
};
