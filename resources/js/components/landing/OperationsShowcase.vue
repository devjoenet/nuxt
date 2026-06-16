<script setup lang="ts">
  import MobileCheckInPreview from "@/components/landing/previews/MobileCheckInPreview.vue";
  import PermissionsPreview from "@/components/landing/previews/PermissionsPreview.vue";

  defineProps<{
    heroImageSrc: string;
  }>();
</script>

<template>
  <div class="grid gap-6 xl:grid-cols-[1.25fr_0.75fr] xl:items-center" aria-label="Examples of operations software interfaces">
    <PermissionsPreview />

    <div class="relative mx-auto w-full max-w-sm">
      <img :src="heroImageSrc" alt="" class="absolute inset-y-10 right-0 hidden h-[72%] w-56 rounded-lg object-cover opacity-35 xl:block" />
      <MobileCheckInPreview />
    </div>
  </div>
</template>
