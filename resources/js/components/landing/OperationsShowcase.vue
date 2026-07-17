<script setup lang="ts">
  import MobileCheckInPreview from "@/components/landing/previews/MobileCheckInPreview.vue";
  import PermissionsPreview from "@/components/landing/previews/PermissionsPreview.vue";

  defineProps<{
    heroImageSrc: string;
  }>();
</script>

<template>
  <div class="grid gap-6 xl:grid-cols-[1.25fr_0.75fr] xl:items-center" aria-label="Examples of operations software interfaces">
    <motion.div :initial="{ opacity: 0, x: -24 }" :transition="{ duration: 0.75, ease: 'easeOut' }" :viewport="{ once: true, amount: 0.25 }" :while-in-view="{ opacity: 1, x: 0 }">
      <PermissionsPreview />
    </motion.div>

    <motion.div
      :initial="{ opacity: 0, x: 24, rotate: 2 }"
      :transition="{ duration: 0.8, delay: 0.12, ease: 'easeOut' }"
      :viewport="{ once: true, amount: 0.25 }"
      :while-in-view="{ opacity: 1, x: 0, rotate: 0 }"
      class="relative mx-auto w-full max-w-sm"
    >
      <img :src="heroImageSrc" alt="" class="absolute inset-y-10 right-0 hidden h-[72%] w-56 rounded-lg border border-secondary-500/20 object-cover opacity-55 xl:block" />
      <MobileCheckInPreview />
    </motion.div>
  </div>
</template>
