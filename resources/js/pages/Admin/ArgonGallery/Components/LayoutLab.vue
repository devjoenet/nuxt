<script setup lang="ts">
import { FormSelect } from "@/components/Ui/FormSelect";
import { ref } from "vue";

type LayoutId = "public" | "authentication" | "application" | "settings" | "gallery";
const layout = ref<LayoutId>("application");
const navigationOpen = ref(true);
const layouts = ["public", "authentication", "application", "settings", "gallery"].map((value) => ({ label: value, value }));
</script>

<template>
  <section aria-labelledby="layout-lab-title" class="grid gap-5">
    <div>
      <h3 id="layout-lab-title" class="text-balance text-lg font-semibold text-content-heading">Layout compositions</h3>
      <p class="text-pretty text-base text-content-body sm:text-sm">Preview inert shell geometry without nesting a second application or exposing product actions.</p>
    </div>
    <div class="grid gap-3 sm:grid-cols-2"><FormSelect v-model="layout" :items="layouts" label="Layout shell" name="layout-shell" /><UCheckbox v-model="navigationOpen" label="Show navigation region" /></div>
    <div class="grid min-h-72 overflow-hidden rounded-xl border border-content-border" :class="navigationOpen && !['public', 'authentication'].includes(layout) ? 'grid-cols-[5rem_minmax(0,1fr)] sm:grid-cols-[12rem_minmax(0,1fr)]' : 'grid-cols-1'">
      <aside v-if="navigationOpen && !['public', 'authentication'].includes(layout)" class="border-r border-content-border bg-surface-subtle p-3">
        <p class="text-base font-medium text-content-heading sm:text-sm">{{ layout }}</p>
        <div class="grid gap-2 pt-4"><USkeleton class="h-8" /><USkeleton class="h-8" /><USkeleton class="h-8" /></div>
      </aside>
      <div class="min-w-0 bg-surface-canvas">
        <header class="flex min-h-14 items-center border-b border-content-border px-4">
          <p class="truncate text-base font-medium text-content-heading sm:text-sm">{{ layout }} shell</p>
        </header>
        <main class="grid gap-4 p-4">
          <USkeleton class="h-8 w-2/3" />
          <div class="grid gap-3 sm:grid-cols-2"><USkeleton class="h-24" /><USkeleton class="h-24" /></div>
          <USkeleton class="h-20" />
        </main>
      </div>
    </div>
  </section>
</template>
