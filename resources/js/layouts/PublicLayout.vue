<script setup lang="ts">
import { LandingFooter } from "@/components/LandingFooter";
import { LandingHeader } from "@/components/LandingHeader";
import { MotionConfig } from "motion-v";

interface FooterLink {
  label: string;
  href: string;
}

interface FooterColumn {
  title: string;
  links: FooterLink[];
}

withDefaults(defineProps<{ footerColumns?: FooterColumn[] }>(), {
  footerColumns: () => [],
});
</script>

<template>
  <MotionConfig reduced-motion="user">
    <div class="min-h-screen overflow-hidden bg-surface-canvas text-content-body selection:bg-primary-500 selection:text-primary-950">
      <LandingHeader />
      <slot />
      <LandingFooter :columns="footerColumns" />
    </div>
  </MotionConfig>
</template>
