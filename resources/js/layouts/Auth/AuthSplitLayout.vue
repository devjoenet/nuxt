<script setup lang="ts">
import AppLogoIcon from "@/components/AppLogoIcon.vue";
import { home } from "@/routes";
import { Link, usePage } from "@inertiajs/vue3";

const page = usePage();
const name = page.props.name;
const quote = page.props.quote;

defineProps<{
  title?: string;
  description?: string;
}>();
</script>

<template>
  <div class="relative grid h-dvh flex-col items-center justify-center px-8 sm:px-0 lg:max-w-none lg:grid-cols-2 lg:px-0">
    <div class="relative hidden h-full flex-col bg-inverted p-10 text-inverted lg:flex dark:border-r dark:border-default">
      <div class="absolute inset-0 bg-inverted" />
      <Link :href="home()" class="relative z-20 flex items-center text-lg font-medium">
        <AppLogoIcon class="mr-2 size-8 fill-current" />
        {{ name }}
      </Link>
      <div v-if="quote" class="relative z-20 mt-auto">
        <blockquote class="space-y-2">
          <p class="text-lg">&ldquo;{{ quote.message }}&rdquo;</p>
          <footer class="text-sm text-inverted/70">{{ quote.author }}</footer>
        </blockquote>
      </div>
    </div>
    <div class="lg:p-8">
      <div class="mx-auto flex w-full flex-col justify-center space-y-6 sm:w-[350px]">
        <div class="flex flex-col space-y-2 text-center">
          <h1 class="text-xl font-medium tracking-tight" v-if="title">{{ title }}</h1>
          <p v-if="description" class="text-sm text-muted">{{ description }}</p>
        </div>
        <slot />
      </div>
    </div>
  </div>
</template>
