import { defineAsyncComponent, type Component } from "vue";

const identityProjectExamples: Record<string, Component> = {
  "account-billing": defineAsyncComponent(() => import("../Examples/Account/Billing.vue")),
  "account-invoice": defineAsyncComponent(() => import("../Examples/Account/Invoice.vue")),
  "account-security": defineAsyncComponent(() => import("../Examples/Account/Security.vue")),
  "account-settings": defineAsyncComponent(() => import("../Examples/Account/Settings.vue")),
  "authentication-error-404": defineAsyncComponent(() => import("../Examples/Authentication/Error/Error404.vue")),
  "authentication-error-500": defineAsyncComponent(() => import("../Examples/Authentication/Error/Error500.vue")),
  "authentication-lock-basic": defineAsyncComponent(() => import("../Examples/Authentication/Lock/Basic.vue")),
  "authentication-lock-cover": defineAsyncComponent(() => import("../Examples/Authentication/Lock/Cover.vue")),
  "authentication-lock-illustration": defineAsyncComponent(() => import("../Examples/Authentication/Lock/Illustration.vue")),
  "authentication-reset-basic": defineAsyncComponent(() => import("../Examples/Authentication/Reset/Basic.vue")),
  "authentication-reset-cover": defineAsyncComponent(() => import("../Examples/Authentication/Reset/Cover.vue")),
  "authentication-reset-illustration": defineAsyncComponent(() => import("../Examples/Authentication/Reset/Illustration.vue")),
  "authentication-signin-basic": defineAsyncComponent(() => import("../Examples/Authentication/SignIn/Basic.vue")),
  "authentication-signin-cover": defineAsyncComponent(() => import("../Examples/Authentication/SignIn/Cover.vue")),
  "authentication-signin-illustration": defineAsyncComponent(() => import("../Examples/Authentication/SignIn/Illustration.vue")),
  "authentication-signup-basic": defineAsyncComponent(() => import("../Examples/Authentication/SignUp/Basic.vue")),
  "authentication-signup-cover": defineAsyncComponent(() => import("../Examples/Authentication/SignUp/Cover.vue")),
  "authentication-signup-illustration": defineAsyncComponent(() => import("../Examples/Authentication/SignUp/Illustration.vue")),
  "authentication-verification-basic": defineAsyncComponent(() => import("../Examples/Authentication/Verification/Basic.vue")),
  "authentication-verification-cover": defineAsyncComponent(() => import("../Examples/Authentication/Verification/Cover.vue")),
  "authentication-verification-illustration": defineAsyncComponent(() => import("../Examples/Authentication/Verification/Illustration.vue")),
  "projects-general": defineAsyncComponent(() => import("../Examples/Projects/General.vue")),
  "projects-new-project": defineAsyncComponent(() => import("../Examples/Projects/NewProject.vue")),
  "projects-timeline": defineAsyncComponent(() => import("../Examples/Projects/Timeline.vue")),
  "team-new-user": defineAsyncComponent(() => import("../Examples/Team/NewUser.vue")),
  "team-overview": defineAsyncComponent(() => import("../Examples/Team/Overview.vue")),
  "team-projects": defineAsyncComponent(() => import("../Examples/Team/Projects.vue")),
  "team-reports": defineAsyncComponent(() => import("../Examples/Team/Reports.vue")),
  "team-teams": defineAsyncComponent(() => import("../Examples/Team/Teams.vue")),
};

export function resolveIdentityProjectExample(id: string): Component | null {
  return identityProjectExamples[id] ?? null;
}
