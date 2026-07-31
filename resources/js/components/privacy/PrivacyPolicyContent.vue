<script lang="ts" setup>
import { home } from "@/routes";
import { Link } from "@inertiajs/vue3";

const emit = defineEmits<{
  contactLinkActivated: [];
}>();
</script>

<template>
  <div class="space-y-8 text-base leading-7 text-muted-700 dark:text-neutral-300">
    <section>
      <h2 class="text-xl font-semibold text-muted-950 dark:text-neutral-50">Information we collect</h2>
      <p class="mt-2">When you submit a project inquiry, we collect the contact, organization, project, attribution, and consent details you provide. We may also record your IP address and browser user agent for security and spam prevention.</p>
    </section>
    <section>
      <h2 class="text-xl font-semibold text-muted-950 dark:text-neutral-50">How we use it</h2>
      <p class="mt-2">We use inquiry information to review your request, contact you about the work, protect the form from abuse, and maintain an internal record of the conversation. We do not sell this information.</p>
    </section>
    <section>
      <h2 class="text-xl font-semibold text-muted-950 dark:text-neutral-50">Retention and requests</h2>
      <p class="mt-2">
        We retain inquiry information while it is useful for the business relationship and legal obligations. To request access, correction, or deletion, use our
        <Link :href="`${home()}#contact`" class="font-semibold text-secondary-700 underline dark:text-secondary-300" @click="emit('contactLinkActivated')">contact form</Link>.
      </p>
    </section>
  </div>
</template>
