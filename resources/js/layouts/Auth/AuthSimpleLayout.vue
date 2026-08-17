<script setup lang="ts">
import AppLogoIcon from "@/components/AppLogoIcon.vue";
import type { CardVariant, PageCardStyle } from "@/lib/cardAppearance";
import { home } from "@/routes";
import { Link } from "@inertiajs/vue3";

defineProps<{
  title?: string;
  description?: string;
  style?: PageCardStyle;
  variant?: CardVariant;
}>();
</script>

<template>
  <div class="flex min-h-svh flex-col items-center justify-center gap-6 p-6 md:p-10">
    <AppPageCard :style="style" :variant="variant" class="w-full max-w-sm">
      <div class="flex flex-col gap-8">
        <div class="flex flex-col items-center gap-4">
          <Link :href="home()" class="flex flex-col items-center gap-2 font-medium">
            <div class="mb-1 flex h-9 w-9 items-center justify-center rounded-md">
              <AppLogoIcon class="size-9 fill-current text-highlighted" />
            </div>
            <span class="sr-only">{{ title }}</span>
          </Link>
          <div class="space-y-2 text-center">
            <h1 class="text-xl font-medium">{{ title }}</h1>
            <p class="text-center text-sm text-muted">{{ description }}</p>
          </div>
        </div>
        <slot />
      </div>
    </AppPageCard>
  </div>
</template>
