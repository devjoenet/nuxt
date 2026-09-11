<script setup lang="ts">
import { AppLogo } from "@/components/AppLogo";
import { AppearanceMenu } from "@/components/AppearanceMenu";
import { Card } from "@/components/Ui/Card";
import { home, privacy } from "@/routes";
import { Link } from "@inertiajs/vue3";

withDefaults(
  defineProps<{
    title?: string;
    description?: string;
  }>(),
  {
    title: "Welcome back",
    description: "Sign in to continue to your workspace.",
  },
);
</script>

<template>
  <div class="grid min-h-svh bg-surface-canvas lg:grid-cols-[minmax(20rem,0.82fr)_minmax(28rem,1.18fr)]">
    <aside class="relative hidden overflow-hidden bg-primary-950 p-10 text-white lg:flex lg:flex-col lg:justify-between" aria-label="Southeast Code">
      <div aria-hidden="true" class="absolute inset-0 bg-linear-to-br from-primary-700 via-primary-950 to-secondary-950 opacity-90" />
      <Link :href="home()" class="relative z-10 w-52 rounded-md focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-white">
        <AppLogo code-variant-class="fill-secondary-300" hr-variant-class="stroke-secondary-300" icon-class="w-full" logo-variant-class="fill-white" southeast-variant-class="fill-white" />
        <span class="sr-only">Southeast Code home</span>
      </Link>
      <div class="relative z-10 max-w-md">
        <p class="text-xs font-bold tracking-[0.18em] text-secondary-300 uppercase">Purpose-built operations software</p>
        <p class="mt-4 text-3xl font-semibold tracking-tight text-white">A clearer way into the work that matters.</p>
        <p class="mt-4 max-w-sm text-sm leading-6 text-primary-100">Secure access to the Southeast Code workspace, shaped around your team and permissions.</p>
      </div>
    </aside>

    <main class="flex min-h-svh flex-col p-4 sm:p-8 lg:p-10">
      <div class="flex items-center justify-between">
        <Link :href="home()" class="w-40 rounded-md lg:hidden">
          <AppLogo code-variant-class="fill-secondary" hr-variant-class="stroke-secondary" icon-class="w-full" logo-variant-class="fill-primary" southeast-variant-class="fill-content-heading" />
          <span class="sr-only">Southeast Code home</span>
        </Link>
        <span class="hidden lg:block" />
        <AppearanceMenu />
      </div>

      <div class="flex flex-1 items-center justify-center py-10">
        <Card as="section" class="w-full max-w-md [--form-field-label-background:var(--argon-surface-elevated)]" data-testid="auth-shell-card" padding="lg" variant="elevated">
          <header class="mb-8 space-y-2">
            <p class="text-xs font-bold tracking-[0.16em] text-primary uppercase">Secure workspace</p>
            <h1 class="text-2xl font-semibold tracking-tight text-content-heading">{{ title }}</h1>
            <p class="text-sm leading-6 text-content-subtle">{{ description }}</p>
          </header>
          <slot />
        </Card>
      </div>

      <footer class="flex items-center justify-between text-xs text-content-subtle">
        <span>&copy; {{ new Date().getFullYear() }} Southeast Code, LLC.</span>
        <Link :href="privacy()" class="rounded-sm hover:text-content-heading focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-primary">Privacy</Link>
      </footer>
    </main>
  </div>
</template>
