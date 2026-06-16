<script setup lang="ts">
  import { Camera, Wifi } from "lucide-vue-next";
</script>

<template>
  <article
    class="relative mx-auto w-full max-w-[16rem] rounded-[2rem] border border-brand-warning-300/40 bg-muted-950 p-2 shadow-2xl shadow-muted-950/70"
    aria-label="Field check-in mobile preview"
  >
    <div class="rounded-[1.55rem] border border-neutral-100/10 bg-muted-900 p-4">
      <div class="flex items-center justify-between">
        <h2 class="text-xs font-semibold text-neutral-100">Field check-in</h2>
        <p class="flex items-center gap-1 text-[0.65rem] text-brand-primary-300">
          <span class="size-1.5 rounded-full bg-brand-primary-300"></span>
          Online
        </p>
      </div>

      <dl class="mt-5 space-y-3 text-xs">
        <div class="grid grid-cols-[4.5rem_1fr] gap-2">
          <dt class="text-neutral-500">Delivery</dt>
          <dd class="text-neutral-200">22-1001</dd>
        </div>
        <div class="grid grid-cols-[4.5rem_1fr] gap-2">
          <dt class="text-neutral-500">Location</dt>
          <dd class="text-neutral-200">Savannah, GA</dd>
        </div>
        <div class="grid grid-cols-[4.5rem_1fr] gap-2">
          <dt class="text-neutral-500">Stop</dt>
          <dd class="text-neutral-200">Stop 6</dd>
        </div>
        <div class="grid grid-cols-[4.5rem_1fr] gap-2">
          <dt class="text-neutral-500">Warehouse</dt>
          <dd class="text-neutral-200">Warehouse 12</dd>
        </div>
        <div class="grid grid-cols-[4.5rem_1fr] gap-2">
          <dt class="text-neutral-500">Status</dt>
          <dd><span class="rounded bg-brand-primary-500 px-2 py-1 text-[0.65rem] font-bold text-muted-950">Arrived</span></dd>
        </div>
      </dl>

      <div class="mt-5 space-y-3">
        <div class="rounded-md border border-neutral-100/10 bg-muted-950/70 p-3">
          <p class="text-[0.65rem] text-neutral-500">Notes</p>
          <p class="mt-2 text-xs text-neutral-300">Dock 4 receiving.</p>
        </div>

        <div class="grid gap-2">
          <span class="flex items-center justify-center gap-2 rounded-md bg-brand-primary-500 px-3 py-2 text-xs font-bold text-muted-950">
            <Camera class="size-4" aria-hidden="true" />
            Capture photo
          </span>
          <span class="flex items-center justify-center gap-2 rounded-md border border-brand-primary-300/30 px-3 py-2 text-xs font-semibold text-brand-primary-200">
            <Wifi class="size-4" aria-hidden="true" />
            Complete check-in
          </span>
        </div>
      </div>
    </div>
  </article>
</template>
