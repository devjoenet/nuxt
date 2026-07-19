<script lang="ts" setup>
  import LandingFooter from "@/components/landing/LandingFooter.vue";
  import LandingHeader from "@/components/landing/LandingHeader.vue";
  import PrivacyPolicyContent from "@/components/privacy/PrivacyPolicyContent.vue";
  import { Head } from "@inertiajs/vue3";

  defineProps<{ canonicalUrl: string }>();
</script>

<template>
  <Head title="Privacy Notice">
    <meta content="How Southeast Code handles information submitted through its website." head-key="description" name="description" />
    <link :href="canonicalUrl" head-key="canonical" rel="canonical" />
  </Head>
  <div class="min-h-screen bg-muted-50 text-muted-900 dark:bg-muted-950 dark:text-neutral-100">
    <LandingHeader />
    <main class="mx-auto max-w-3xl px-5 pt-36 pb-20 sm:px-8">
      <p class="text-sm font-bold tracking-[0.2em] text-secondary-600 uppercase dark:text-secondary-300">Privacy</p>
      <h1 class="mt-3 text-4xl font-bold">Privacy notice</h1>
      <PrivacyPolicyContent class="mt-8" />
    </main>
    <LandingFooter :columns="[]" />
  </div>
</template>
