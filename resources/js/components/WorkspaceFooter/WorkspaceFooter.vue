<script setup lang="ts">
import { privacy } from "@/routes";
import { Link } from "@inertiajs/vue3";

const year = new Date().getFullYear();
</script>

<template>
  <footer class="mt-auto flex flex-col gap-2 border-t border-content-border px-4 py-5 text-xs text-content-subtle sm:flex-row sm:items-center sm:justify-between sm:px-6" data-testid="workspace-footer">
    <p>&copy; {{ year }} Southeast Code, LLC.</p>
    <Link :href="privacy()" class="rounded-sm transition hover:text-content-heading focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-primary">Privacy</Link>
  </footer>
</template>
