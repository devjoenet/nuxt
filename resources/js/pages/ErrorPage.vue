<script lang="ts" setup>
import { AppLogo } from "@/components/AppLogo";
import { dashboard, home } from "@/routes";
import { Head, Link, usePage } from "@inertiajs/vue3";
import { useColorMode } from "@vueuse/core";
import { computed } from "vue";
import type { AppPageProps } from "@/types";

type ErrorStatus = 403 | 404 | 419 | 429 | 500 | 503;

interface ErrorContent {
  title: string;
  description: string;
}

const props = defineProps<{ status: number }>();
const page = usePage<AppPageProps>();
useColorMode();

const copy = {
  403: {
    title: "You do not have access to this workspace area",
    description: "Your account cannot open this area. Return to the workspace or contact an administrator if you need access.",
  },
  404: {
    title: "That page is not here",
    description: "The address may be outdated or the page may have moved. Try returning to a page you know is available.",
  },
  419: {
    title: "This request expired",
    description: "Your session expired before the request completed. Return to a safe page before trying again.",
  },
  429: {
    title: "Too many requests",
    description: "We received too many requests in a short time. Please wait briefly and try again.",
  },
  500: {
    title: "Something went wrong",
    description: "We could not complete that request, and the issue was recorded. Please try again shortly.",
  },
  503: {
    title: "Southeast Code is temporarily unavailable",
    description: "The service is unavailable during maintenance or a service interruption. Please try again shortly.",
  },
} satisfies Record<ErrorStatus, ErrorContent>;

const status = computed<ErrorStatus>(() => {
  const supportedStatuses: ErrorStatus[] = [403, 404, 419, 429, 500, 503];

  return supportedStatuses.includes(props.status as ErrorStatus) ? (props.status as ErrorStatus) : 500;
});
const message = computed(() => copy[status.value]);
const isAuthenticated = computed(() => page.props.auth.user !== null);
</script>

<template>
  <Head :title="`${status} — ${message.title}`">
    <meta content="noindex, nofollow" name="robots" />
  </Head>

  <main class="relative flex min-h-dvh min-h-screen items-center justify-center overflow-hidden bg-default px-4 py-8 text-default sm:px-6 sm:py-12">
    <div aria-hidden="true" class="pointer-events-none absolute -top-32 -left-24 size-80 rounded-full bg-primary-500/10 blur-3xl dark:bg-primary-400/10"></div>
    <div aria-hidden="true" class="pointer-events-none absolute -right-24 -bottom-32 size-96 rounded-full bg-secondary-500/10 blur-3xl dark:bg-secondary-400/10"></div>

    <section aria-labelledby="error-title" class="relative w-full max-w-2xl rounded-2xl border border-default bg-elevated/90 p-6 shadow-2xl ring-1 shadow-slate-950/10 ring-default/60 sm:p-10 dark:shadow-none">
      <Link :href="home.url()" aria-label="Southeast Code home" class="inline-flex max-w-full rounded-lg focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-primary-500">
        <AppLogo
          :icon-class="'h-auto w-44 max-w-full sm:w-56'"
          aria-hidden="true"
          code-variant-class="fill-brand-accent-600 dark:fill-brand-accent-400"
          hr-variant-class="stroke-brand-accent-600 dark:stroke-brand-accent-400"
          logo-variant-class="fill-brand-600 dark:fill-brand-400"
          southeast-variant-class="fill-slate-950 dark:fill-slate-50"
        />
      </Link>

      <div class="mt-12 max-w-xl">
        <p class="text-sm font-semibold tracking-[0.2em] text-primary-800 uppercase dark:text-primary-300">{{ status }}</p>
        <h1 id="error-title" class="mt-4 text-3xl leading-tight font-semibold text-highlighted sm:text-4xl">{{ message.title }}</h1>
        <p class="mt-4 text-base leading-7 text-muted">{{ message.description }}</p>
      </div>

      <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:items-center">
        <Link
          v-if="isAuthenticated"
          :href="dashboard.url()"
          class="inline-flex w-full items-center justify-center rounded-lg bg-primary-500 px-5 py-3 text-sm font-semibold text-primary-950 transition hover:bg-primary-400 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-500 sm:w-auto"
        >
          Return to workspace
        </Link>
        <Link
          :href="home.url()"
          class="inline-flex w-full items-center justify-center rounded-lg border border-default px-5 py-3 text-sm font-semibold text-highlighted transition hover:bg-default focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-500 sm:w-auto"
        >
          Southeast Code homepage
        </Link>
      </div>
    </section>
  </main>
</template>
