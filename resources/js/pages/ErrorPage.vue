<script lang="ts" setup>
  import { Head, Link } from "@inertiajs/vue3";
  import { dashboard, home } from "@/routes";

  const props = defineProps<{ status: number }>();

  const copy: Record<number, { title: string; description: string }> = {
    403: { title: "You do not have access to this page", description: "Your account does not have the permission required for this workspace area." },
    404: { title: "That page is not here", description: "The link may be outdated or the page may have moved." },
    419: { title: "This page expired", description: "Refresh the page and try the action again." },
    429: { title: "Too many requests", description: "Please wait a moment before trying again." },
    500: { title: "Something went wrong", description: "The error has been recorded. Please try again shortly." },
    503: { title: "Temporarily unavailable", description: "The application is being updated or is under maintenance." },
  };

  const message = copy[props.status] ?? copy[500];
</script>

<template>
  <Head :title="`${status} — ${message.title}`" />

  <main class="flex min-h-screen items-center justify-center bg-muted-50 px-6 py-16 dark:bg-muted-950">
    <div class="w-full max-w-lg text-center">
      <p class="text-sm font-semibold tracking-[0.2em] text-primary-600 uppercase dark:text-primary-300">{{ status }}</p>
      <h1 class="mt-4 text-3xl font-semibold text-highlighted sm:text-4xl">{{ message.title }}</h1>
      <p class="mx-auto mt-4 max-w-md text-base leading-7 text-muted">{{ message.description }}</p>
      <div class="mt-8 flex flex-wrap justify-center gap-3">
        <Link :href="dashboard.url()" class="rounded-md bg-primary px-4 py-2 text-sm font-semibold text-inverted hover:bg-primary/90">Return to workspace</Link>
        <Link :href="home.url()" class="rounded-md px-4 py-2 text-sm font-semibold text-primary hover:bg-elevated">Go to homepage</Link>
      </div>
    </div>
  </main>
</template>
