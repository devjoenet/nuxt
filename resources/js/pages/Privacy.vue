<script lang="ts" setup>
  import LandingFooter from "@/components/landing/LandingFooter.vue";
  import LandingHeader from "@/components/landing/LandingHeader.vue";
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
      <div class="mt-8 space-y-8 text-base leading-7 text-muted-700 dark:text-neutral-300">
        <section>
          <h2 class="text-xl font-semibold text-muted-950 dark:text-neutral-50">Information we collect</h2>
          <p class="mt-2">
            When you submit a project inquiry, we collect the contact, organization, project, attribution, and consent details you provide. We may also record your IP address and
            browser user agent for security and spam prevention.
          </p>
        </section>
        <section>
          <h2 class="text-xl font-semibold text-muted-950 dark:text-neutral-50">How we use it</h2>
          <p class="mt-2">
            We use inquiry information to review your request, contact you about the work, protect the form from abuse, and maintain an internal record of the conversation. We do
            not sell this information.
          </p>
        </section>
        <section>
          <h2 class="text-xl font-semibold text-muted-950 dark:text-neutral-50">Retention and requests</h2>
          <p class="mt-2">
            We retain inquiry information while it is useful for the business relationship and legal obligations. To request access, correction, or deletion, email
            <a class="font-semibold text-secondary-700 underline dark:text-secondary-300" href="mailto:hello@southeastcode.com">hello@southeastcode.com</a>.
          </p>
        </section>
      </div>
    </main>
    <LandingFooter :columns="[]" />
  </div>
</template>
