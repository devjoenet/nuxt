<script lang="ts" setup>
import PrivacyPolicyContent from "@/components/Privacy/PrivacyPolicyContent.vue";
import { Head, setLayoutProps } from "@inertiajs/vue3";

defineProps<{ canonicalUrl: string }>();
setLayoutProps({ footerColumns: [] });
</script>

<template>
  <div class="contents">
    <Head title="Privacy Notice">
      <meta content="How Southeast Code handles information submitted through its website." head-key="description" name="description" />
      <link :href="canonicalUrl" head-key="canonical" rel="canonical" />
    </Head>
    <main class="mx-auto max-w-3xl px-5 pt-36 pb-20 sm:px-8">
      <p class="text-sm font-bold tracking-[0.2em] text-secondary-600 uppercase dark:text-secondary-300">Privacy</p>
      <h1 class="mt-3 text-4xl font-bold">Privacy notice</h1>
      <PrivacyPolicyContent class="mt-8" />
    </main>
  </div>
</template>
