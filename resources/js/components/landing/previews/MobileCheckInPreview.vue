<script lang="ts" setup>
  import { Motion } from "motion-v";
</script>

<template>
  <Motion
    as="article"
    :initial="{ opacity: 0, y: 24, rotate: 2 }"
    :transition="{ duration: 0.75, ease: 'easeOut' }"
    :viewport="{ once: true, amount: 0.2 }"
    :while-in-view="{ opacity: 1, y: 0, rotate: 0 }"
    class="relative mx-auto w-full max-w-[16rem] rounded-[2rem] border border-warning-300/40 bg-muted-100 p-2 shadow-2xl shadow-muted-950/16 dark:bg-muted-950 dark:shadow-muted-950/70"
    aria-label="Field check-in mobile preview"
  >
    <div class="rounded-[1.55rem] border border-muted-200 bg-muted-50 p-4 dark:border-neutral-100/10 dark:bg-muted-900">
      <div class="flex items-center justify-between">
        <h2 class="text-xs font-semibold text-muted-900 dark:text-neutral-100">Field check-in</h2>
        <p class="flex items-center gap-1 text-[0.65rem] text-secondary-400">
          <span class="size-1.5 rounded-full bg-secondary-500"></span>
          Online
        </p>
      </div>

      <dl class="mt-5 space-y-3 text-xs">
        <div class="grid grid-cols-[4.5rem_1fr] gap-2">
          <dt class="text-muted-500 dark:text-neutral-500">Delivery</dt>
          <dd class="text-muted-800 dark:text-neutral-200">22-1001</dd>
        </div>
        <div class="grid grid-cols-[4.5rem_1fr] gap-2">
          <dt class="text-muted-500 dark:text-neutral-500">Location</dt>
          <dd class="text-muted-800 dark:text-neutral-200">Savannah, GA</dd>
        </div>
        <div class="grid grid-cols-[4.5rem_1fr] gap-2">
          <dt class="text-muted-500 dark:text-neutral-500">Stop</dt>
          <dd class="text-muted-800 dark:text-neutral-200">Stop 6</dd>
        </div>
        <div class="grid grid-cols-[4.5rem_1fr] gap-2">
          <dt class="text-muted-500 dark:text-neutral-500">Warehouse</dt>
          <dd class="text-muted-800 dark:text-neutral-200">Warehouse 12</dd>
        </div>
        <div class="grid grid-cols-[4.5rem_1fr] gap-2">
          <dt class="text-muted-500 dark:text-neutral-500">Status</dt>
          <dd><span class="rounded bg-primary-500 px-2 py-1 text-[0.65rem] font-bold text-primary-950">Arrived</span></dd>
        </div>
      </dl>

      <div class="mt-5 space-y-3">
        <div class="rounded-md border border-muted-200 bg-muted-100/80 p-3 dark:border-neutral-100/10 dark:bg-muted-950/70">
          <p class="text-[0.65rem] text-muted-500 dark:text-neutral-500">Notes</p>
          <p class="mt-2 text-xs text-muted-700 dark:text-neutral-300">Dock 4 receiving.</p>
        </div>

        <div class="grid gap-2">
          <span class="flex items-center justify-center gap-2 rounded-md bg-secondary-500 px-3 py-2 text-xs font-bold text-secondary-50">
            <UIcon aria-hidden="true" class="size-4" name="i-ph-camera" />
            Capture photo
          </span>
          <span class="flex items-center justify-center gap-2 rounded-md border border-accent-500/35 px-3 py-2 text-xs font-semibold text-accent-700 dark:text-accent-100">
            <UIcon aria-hidden="true" class="size-4" name="i-ph-wifi-high" />
            Complete check-in
          </span>
        </div>
      </div>
    </div>
  </Motion>
</template>
