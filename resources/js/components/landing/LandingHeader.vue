<script setup lang="ts">
  import { dashboard } from "@/routes";
  import { Link } from "@inertiajs/vue3";

  defineProps<{
    logoSrc: string;
  }>();

  const navigation = [
    { label: "Services", href: "#services" },
    { label: "Process", href: "#process" },
    { label: "Workflows", href: "#operations" },
    { label: "About", href: "#impact" },
  ];
</script>

<template>
  <header class="absolute inset-x-0 top-0 z-30 px-5 py-5 sm:px-8">
    <div class="mx-auto flex max-w-7xl items-center justify-between gap-6">
      <a href="#hero" class="flex items-center rounded-md focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand-primary-300">
        <img :src="logoSrc" alt="Southeast Code" class="h-12 w-auto sm:h-16" />
      </a>

      <nav class="hidden items-center gap-8 text-sm font-medium text-neutral-200 md:flex" aria-label="Primary navigation">
        <a
          v-for="item in navigation"
          :key="item.href"
          :href="item.href"
          class="transition hover:text-brand-primary-300 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand-primary-300"
        >
          {{ item.label }}
        </a>
      </nav>

      <Link
        v-if="$page.props.auth.user"
        :href="dashboard()"
        class="hidden rounded-md border border-brand-primary-300/70 px-4 py-2 text-sm font-semibold text-brand-primary-200 transition hover:bg-brand-primary-400/10 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-primary-300 sm:inline-flex"
      >
        Dashboard
      </Link>
      <a
        v-else
        href="#contact"
        class="inline-flex rounded-md bg-brand-primary-500 px-4 py-2 text-sm font-bold text-muted-950 shadow-lg shadow-brand-primary-500/20 transition hover:bg-brand-primary-400 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-primary-200"
      >
        Map a workflow
        <span aria-hidden="true" class="ml-2">-></span>
      </a>
    </div>
  </header>
</template>
