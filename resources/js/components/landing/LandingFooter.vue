<script setup lang="ts">
  import AppLogo from "@/components/AppLogo.vue";
  import { PhLinkedinLogo, PhGithubLogo, PhEnvelopeSimple } from "@phosphor-icons/vue";

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
  <footer class="bg-muted-100 px-5 py-12 sm:px-8 dark:bg-muted-950">
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
            <p><a class="transition hover:text-accent-400" href="mailto:hello@southeastcode.com">hello@southeastcode.com</a></p>
            <p><a class="transition hover:text-accent-400" href="tel:+19125550144">(912) 555-0144</a></p>
          </address>
          <div class="mt-5 flex gap-4 text-muted-600 dark:text-neutral-400">
            <a
              aria-label="GitHub"
              class="inline-flex items-center justify-center rounded-md border px-1 py-1 transition duration-300 hover:border-indigo-600 hover:text-indigo-600 hover:bg-indigo-200/10"
              href="#"
              ><PhGithubLogo class="size-5" weight="fill"
            /></a>
            <a
              aria-label="LinkedIn"
              class="inline-flex items-center justify-center rounded-md border px-1 py-1 transition duration-300 hover:border-blue-600 hover:text-blue-600 hover:bg-blue-200/10"
              href="#"
              ><PhLinkedinLogo class="size-5" weight="fill"
            /></a>
            <a
              aria-label="Email"
              class="inline-flex items-center justify-center rounded-md border px-1 py-1 transition duration-300 hover:border-teal-600 hover:text-teal-600 hover:bg-teal-200/10"
              href="mailto:hello@southeastcode.com"
              ><PhEnvelopeSimple class="size-5" weight="fill"
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
        <a class="transition hover:text-muted-800 dark:hover:text-neutral-300" href="#">Privacy</a>
        <a class="transition hover:text-muted-800 dark:hover:text-neutral-300" href="#">Terms</a>
      </div>
    </div>
  </footer>
</template>
