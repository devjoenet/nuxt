<script setup lang="ts">
import AuthLayout from "@/layouts/Auth/AuthSimpleLayout.vue";
import type { CardVariant, PageCardStyle } from "@/lib/cardAppearance";

defineProps<{
  title?: string;
  description?: string;
  style?: PageCardStyle;
  variant?: CardVariant;
}>();
</script>

<template>
  <AuthLayout :description :style :title :variant>
    <slot />
  </AuthLayout>
</template>
