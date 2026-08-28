<script setup lang="ts">
import AppLogo from "@/components/AppLogo.vue";
import { AppearanceMenu } from "@/components/AppearanceMenu";
import { useLandingSectionNavigation, type LandingNavigationItem } from "@/composables/useLandingSectionNavigation";
import { dashboard, home } from "@/routes";
import { Link, usePage } from "@inertiajs/vue3";
import { Motion } from "motion-v";
import { computed, ref } from "vue";

const page = usePage();
const mobileNavigationOpen = ref(false);
const { navigation, activeSection, hasGlassBackground } = useLandingSectionNavigation();
const isLandingPage = computed(() => page.component === "Welcome");
const navigationHasSurface = computed(() => !isLandingPage.value || hasGlassBackground.value || mobileNavigationOpen.value);

function sectionHref(item: LandingNavigationItem): string {
  return isLandingPage.value ? item.href : `${home.url()}${item.href}`;
}

function closeMobileNavigation(): void {
  mobileNavigationOpen.value = false;
}
</script>

<template>
  <Motion as="header" :animate="{ opacity: 1, y: 0 }" :initial="{ opacity: 0, y: -18 }" :transition="{ duration: 0.45, ease: 'easeOut' }" class="fixed inset-x-0 top-0 z-50 px-4 py-3 sm:px-8">
    <div
      :class="navigationHasSurface ? 'border-content-border bg-surface-translucent shadow-lg backdrop-blur-argon' : 'border-transparent bg-transparent shadow-none'"
      class="mx-auto flex min-h-16 w-full max-w-7xl items-center gap-4 rounded-2xl border px-4 transition duration-300 sm:px-6"
    >
      <Link :href="isLandingPage ? '#hero' : home()" aria-label="Southeast Code home" class="w-44 shrink-0 rounded-md focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-primary">
        <AppLogo aria-hidden="true" :southeast-variant-class="navigationHasSurface ? 'fill-content-heading' : 'fill-white'" code-variant-class="fill-secondary" hr-variant-class="stroke-secondary" icon-class="w-full" logo-variant-class="fill-primary" />
      </Link>

      <nav aria-label="Primary navigation" class="mx-auto hidden items-center gap-6 lg:flex">
        <a
          v-for="item in navigation"
          :key="item.href"
          :aria-current="activeSection === item.id ? 'page' : undefined"
          :class="activeSection === item.id ? 'text-primary' : navigationHasSurface ? 'text-content-body hover:text-content-heading' : 'text-white/85 hover:text-white'"
          class="rounded-md px-2 py-2 text-xs font-bold tracking-[0.12em] uppercase transition focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-primary"
          :href="sectionHref(item)"
        >
          {{ item.label }}
        </a>
      </nav>

      <div class="ml-auto flex items-center gap-1 sm:gap-2 lg:ml-0">
        <AppearanceMenu />

        <Link v-if="page.props.auth.user" :href="dashboard()" class="hidden sm:block">
          <UButton color="primary" label="Dashboard" variant="soft" />
        </Link>
        <a v-else :href="isLandingPage ? '#contact' : `${home.url()}#contact`" class="hidden sm:block">
          <UButton color="primary" label="Map a workflow" trailing-icon="i-ph-arrow-circle-right" />
        </a>

        <USlideover v-model:open="mobileNavigationOpen" title="Navigation" description="Browse Southeast Code" side="right">
          <UButton aria-label="Open navigation" color="neutral" data-testid="public-navigation-trigger" icon="i-ph-list" square variant="ghost" class="lg:hidden" />

          <template #body>
            <nav aria-label="Mobile navigation" class="flex flex-col gap-2">
              <a
                v-for="item in navigation"
                :key="item.href"
                :aria-current="activeSection === item.id ? 'page' : undefined"
                :href="sectionHref(item)"
                class="flex min-h-11 items-center rounded-xl px-4 font-semibold text-content-body transition hover:bg-surface-inset hover:text-content-heading focus-visible:outline-2 focus-visible:outline-primary"
                @click="closeMobileNavigation"
              >
                {{ item.label }}
              </a>
            </nav>
          </template>

          <template #footer>
            <Link v-if="page.props.auth.user" :href="dashboard()" class="w-full" @click="closeMobileNavigation">
              <UButton block label="Dashboard" />
            </Link>
            <a v-else :href="isLandingPage ? '#contact' : `${home.url()}#contact`" class="w-full" @click="closeMobileNavigation">
              <UButton block label="Map a workflow" trailing-icon="i-ph-arrow-circle-right" />
            </a>
          </template>
        </USlideover>
      </div>
    </div>
  </Motion>
</template>
