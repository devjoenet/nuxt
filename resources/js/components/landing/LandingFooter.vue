<script setup lang="ts">
  import { Github, Linkedin, Mail, MapPin } from "lucide-vue-next";

  interface FooterLink {
    label: string;
    href: string;
  }

  interface FooterColumn {
    title: string;
    links: FooterLink[];
  }

  defineProps<{
    logoSrc: string;
    columns: FooterColumn[];
  }>();
</script>

<template>
  <footer class="bg-muted-950 px-5 py-12 sm:px-8">
    <div class="mx-auto grid max-w-7xl gap-10 lg:grid-cols-[1.1fr_2fr]">
      <div>
        <img :src="logoSrc" alt="Southeast Code" class="h-16 w-auto" />
        <p class="mt-5 max-w-xs text-sm leading-6 text-neutral-400">Operations-heavy organizations. Built near Savannah, Georgia.</p>
        <p class="mt-5 flex items-center gap-2 text-sm text-brand-primary-300">
          <MapPin class="size-4" aria-hidden="true" />
          Savannah, Georgia, USA
        </p>
      </div>

      <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
        <div v-for="column in columns" :key="column.title">
          <h2 class="text-xs font-bold tracking-[0.18em] text-brand-primary-400 uppercase">{{ column.title }}</h2>
          <ul class="mt-4 space-y-2">
            <li v-for="link in column.links" :key="link.label">
              <a :href="link.href" class="text-sm text-neutral-400 transition hover:text-brand-primary-300">{{ link.label }}</a>
            </li>
          </ul>
        </div>

        <div>
          <h2 class="text-xs font-bold tracking-[0.18em] text-brand-primary-400 uppercase">Let's connect</h2>
          <address class="mt-4 space-y-2 text-sm not-italic text-neutral-400">
            <p><a href="mailto:hello@southeastcode.com" class="transition hover:text-brand-primary-300">hello@southeastcode.com</a></p>
            <p><a href="tel:+19125550144" class="transition hover:text-brand-primary-300">(912) 555-0144</a></p>
          </address>
          <div class="mt-5 flex gap-4 text-neutral-400">
            <a href="#" aria-label="GitHub" class="transition hover:text-brand-primary-300"><Github class="size-5" /></a>
            <a href="#" aria-label="LinkedIn" class="transition hover:text-brand-primary-300"><Linkedin class="size-5" /></a>
            <a href="mailto:hello@southeastcode.com" aria-label="Email" class="transition hover:text-brand-primary-300"><Mail class="size-5" /></a>
          </div>
        </div>
      </div>
    </div>

    <div class="mx-auto mt-10 flex max-w-7xl flex-col gap-3 border-t border-neutral-100/10 pt-6 text-xs text-neutral-500 sm:flex-row sm:items-center sm:justify-between">
      <p>&copy; 2026 Southeast Code, LLC. All rights reserved.</p>
      <div class="flex gap-5">
        <a href="#" class="transition hover:text-neutral-300">Privacy</a>
        <a href="#" class="transition hover:text-neutral-300">Terms</a>
      </div>
    </div>
  </footer>
</template>
