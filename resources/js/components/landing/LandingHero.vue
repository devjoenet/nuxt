<script setup lang="ts">
  import DashboardPreview from "@/components/landing/previews/DashboardPreview.vue";

  defineProps<{
    heroImageSrc: string;
  }>();
</script>

<template>
  <section id="hero" class="relative min-h-screen overflow-hidden px-5 pt-32 pb-16 sm:px-8 lg:pt-36">
    <img :src="heroImageSrc" alt="" class="absolute inset-0 size-full object-cover" />
    <div class="absolute inset-0 bg-[linear-gradient(90deg,rgba(1,11,16,0.98)_0%,rgba(1,11,16,0.78)_34%,rgba(1,11,16,0.52)_62%,rgba(1,11,16,0.84)_100%)]" aria-hidden="true"></div>
    <div
      class="absolute inset-0 bg-[radial-gradient(circle_at_78%_34%,rgba(54,243,225,0.18),transparent_26rem),linear-gradient(180deg,rgba(1,11,16,0.2),rgba(1,11,16,1))]"
      aria-hidden="true"
    ></div>

    <div class="relative mx-auto grid max-w-7xl gap-10 lg:min-h-[calc(100vh-9rem)] lg:grid-cols-[0.78fr_1.22fr] lg:items-center">
      <div class="max-w-2xl">
        <p class="mb-5 text-sm font-bold tracking-[0.26em] text-brand-primary-300 uppercase">Operations-first custom software</p>
        <h1 class="max-w-xl text-5xl leading-[0.95] font-black tracking-tight text-neutral-50 uppercase sm:text-6xl lg:text-7xl">
          Custom software for work that refuses to fit
          <span class="block text-brand-primary-300">a spreadsheet.</span>
        </h1>
        <p class="mt-6 max-w-lg text-base leading-7 text-neutral-200">
          We turn fragmented operations into secure web applications built around how your teams move data, approvals, and decisions.
        </p>

        <div class="mt-8 flex flex-col gap-3 sm:flex-row">
          <a
            href="#contact"
            class="inline-flex items-center justify-center rounded-md bg-brand-primary-500 px-6 py-3 text-sm font-bold text-muted-950 shadow-lg shadow-brand-primary-500/20 transition hover:bg-brand-primary-400 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-primary-200"
          >
            Start a workflow audit
            <span aria-hidden="true" class="ml-2">-></span>
          </a>
          <a
            href="#services"
            class="inline-flex items-center justify-center rounded-md border border-brand-primary-400/80 px-6 py-3 text-sm font-semibold text-brand-primary-100 transition hover:border-brand-primary-300 hover:bg-brand-primary-400/10 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-primary-300"
          >
            See what we build
          </a>
        </div>
      </div>

      <DashboardPreview />
    </div>
  </section>
</template>
