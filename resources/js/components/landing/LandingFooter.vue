<script setup lang="ts">
  import AppLogo from "@/components/AppLogo.vue";
  import { privacy } from "@/routes";
  import { Link } from "@inertiajs/vue3";
  import { Motion } from "motion-v";

  interface FooterLink {
    label: string;
    href: string;
  }

  interface FooterColumn {
    title: string;
    links: FooterLink[];
  }

  defineProps<{
    columns: FooterColumn[];
  }>();

  const columnToneClasses = ["text-primary-400", "text-secondary-400", "text-accent-400"];
</script>

<template>
  <Motion
    as="footer"
    :initial="{ opacity: 0, y: 24 }"
    :transition="{ duration: 0.7, ease: 'easeOut' }"
    :viewport="{ once: true, amount: 0.12 }"
    :while-in-view="{ opacity: 1, y: 0 }"
    class="bg-muted-100 px-5 py-12 sm:px-8 dark:bg-muted-950"
  >
    <div class="mx-auto grid max-w-7xl gap-10 lg:grid-cols-[1.1fr_2fr]">
      <div>
        <AppLogo
          code-variant-class="fill-neutral-950 dark:fill-neutral-50"
          hr-variant-class="stroke-neutral-950 dark:stroke-neutral-50"
          icon-class="w-full max-w-48"
          logo-variant-class="fill-neutral-950 dark:fill-neutral-50"
          southeast-variant-class="fill-neutral-950 dark:fill-neutral-50"
        />
        <p class="mt-5 max-w-xs text-sm leading-6 text-muted-600 dark:text-neutral-400">Operations-heavy organizations. Built near Savannah, Georgia.</p>
        <p class="mt-5 flex items-center gap-2 text-sm text-secondary-400">
          <UIcon aria-hidden="true" class="size-4" name="i-ph-map-pin" />
          Savannah, Georgia, USA
        </p>
      </div>

      <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
        <div v-for="(column, index) in columns" :key="column.title">
          <h2 :class="columnToneClasses[index % columnToneClasses.length]" class="text-xs font-bold tracking-[0.18em] uppercase">{{ column.title }}</h2>
          <ul class="mt-4 space-y-2">
            <li v-for="link in column.links" :key="link.label">
              <a :href="link.href" class="text-sm text-muted-600 transition hover:text-secondary-500 dark:text-neutral-400 dark:hover:text-secondary-400">{{ link.label }}</a>
            </li>
          </ul>
        </div>

        <div>
          <h2 class="text-xs font-bold tracking-[0.18em] text-primary-400 uppercase">Let's connect</h2>
          <address class="mt-4 space-y-2 text-sm not-italic text-muted-600 dark:text-neutral-400">
            <p><a class="transition hover:text-accent-400" href="#contact">Use the contact form</a></p>
          </address>
          <div class="mt-5 flex gap-4 text-muted-600 dark:text-neutral-400">
            <a
              aria-label="Contact form"
              class="inline-flex items-center justify-center rounded-md border px-1 py-1 transition duration-300 hover:border-teal-600 hover:text-teal-600 hover:bg-teal-200/10"
              href="#contact"
              ><UIcon aria-hidden="true" class="size-5" name="i-ph-chat-circle-text"
            /></a>
          </div>
        </div>
      </div>
    </div>

    <div
      class="mx-auto mt-10 flex max-w-7xl flex-col gap-3 border-t border-muted-300 pt-6 text-xs text-muted-500 sm:flex-row sm:items-center sm:justify-between dark:border-neutral-100/10 dark:text-neutral-500"
    >
      <p>&copy; 2026 Southeast Code, LLC. All rights reserved.</p>
      <div class="flex gap-5">
        <Link :href="privacy()" class="transition hover:text-muted-800 dark:hover:text-neutral-300">Privacy</Link>
      </div>
    </div>
  </Motion>
</template>
