<script setup lang="ts">
  import AppLogo from "@/components/AppLogo.vue";
  import { dashboard } from "@/routes";
  import { Link } from "@inertiajs/vue3";
  import { useWindowScroll } from "@vueuse/core";
  import { computed } from "vue";
  import { Motion } from "motion-v";

  const navigation = [
    { id: "reality", label: "Why custom", href: "#reality" },
    { id: "services", label: "Services", href: "#services" },
    { id: "process", label: "Process", href: "#process" },
    { id: "operations", label: "Workflows", href: "#operations" },
  ];

  const { y } = useWindowScroll();

  const hasGlassBackground = computed(() => y.value > 28);

  const activeSection = computed(() => {
    if (typeof document === "undefined") {
      return "";
    }

    const activationLine = y.value + 160;
    const activeItem = navigation
      .map((item) => ({
        id: item.id,
        top: document.getElementById(item.id)?.offsetTop ?? Number.POSITIVE_INFINITY,
      }))
      .filter((section) => section.top <= activationLine)
      .at(-1);

    return activeItem?.id ?? "";
  });
</script>

<template>
  <Motion
    as="header"
    :animate="{ opacity: 1, y: 0 }"
    :initial="{ opacity: 0, y: -18 }"
    :transition="{ duration: 0.6, ease: 'easeOut' }"
    class="fixed inset-x-0 top-0 z-50 px-5 py-3 transition-all duration-500 sm:px-8"
  >
    <div
      :class="
        hasGlassBackground
          ? 'px-5 py-4 shadow-[0_6px_9px_rgba(2,6,23,0.1),0_0_18px_rgba(2,6,23,0.06)] ring-[0.5px] ring-muted-100/12 sm:px-6 sm:py-4 lg:px-7 dark:shadow-[0_6px_14px_rgba(0,0,0,0.18),0_0_18px_rgba(155,167,175,0.03)] dark:ring-muted-700/12'
          : 'px-5 py-2 shadow-none ring-0 sm:px-6 lg:px-7'
      "
      class="relative isolate mx-auto grid w-full max-w-7xl grid-cols-[auto_minmax(0,1fr)_auto] items-center gap-3 overflow-hidden rounded-[1.45rem] border border-transparent transition-all duration-500 ease-out sm:gap-6"
    >
      <span
        :class="hasGlassBackground ? 'opacity-100' : 'opacity-0'"
        aria-hidden="true"
        class="nav-liquid-glass-effect pointer-events-none absolute inset-0 z-0 rounded-[inherit] transition-opacity duration-500 ease-out"
      ></span>
      <span
        :class="
          hasGlassBackground
            ? 'border-[0.5px] border-muted-200/28 bg-muted-50/72 opacity-100 dark:border-muted-700/24 dark:bg-muted-950/78'
            : 'border border-transparent bg-transparent opacity-0'
        "
        aria-hidden="true"
        class="pointer-events-none absolute inset-0 z-1 rounded-[inherit] transition-all duration-500 ease-out"
      ></span>
      <span
        :class="hasGlassBackground ? 'opacity-100' : 'opacity-0'"
        aria-hidden="true"
        class="nav-liquid-glass-shine pointer-events-none absolute inset-0 z-2 rounded-[inherit] transition-opacity duration-500 ease-out"
      ></span>

      <a
        aria-label="Southeast Code home"
        class="relative z-10 flex max-w-43.75 items-center justify-self-start rounded-md focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-primary-400"
        href="#hero"
      >
        <AppLogo
          aria-hidden="true"
          :southeast-variant-class="hasGlassBackground ? 'fill-neutral-950 dark:fill-neutral-50' : 'fill-neutral-50 dark:fill-neutral-50'"
          code-variant-class="fill-secondary"
          hr-variant-class="stroke-secondary"
          icon-class="w-full"
          logo-variant-class="fill-primary"
        />
      </a>

      <nav aria-label="Primary navigation" class="relative z-10 hidden items-center justify-self-center gap-7 text-xs font-bold uppercase tracking-[0.14em] md:flex">
        <a
          v-for="item in navigation"
          :key="item.href"
          :href="item.href"
          :aria-current="activeSection === item.id ? 'true' : undefined"
          :class="
            activeSection === item.id
              ? 'text-primary-700 dark:text-primary-100'
              : hasGlassBackground
                ? 'text-muted-700 hover:text-secondary-700 dark:text-neutral-200 dark:hover:text-secondary-200'
                : 'text-neutral-100 hover:text-secondary-200'
          "
          class="group relative isolate inline-flex h-10 items-center rounded-md px-1 transition-colors duration-300 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-secondary-400"
        >
          <span class="relative z-10">{{ item.label }}</span>
          <svg
            :class="
              activeSection === item.id ? 'scale-x-100 text-primary-400 opacity-100' : 'scale-x-0 text-secondary-300 opacity-0 group-hover:scale-x-100 group-hover:opacity-90'
            "
            aria-hidden="true"
            class="pointer-events-none absolute -bottom-0.5 left-1/2 z-0 h-3 w-[calc(100%+0.85rem)] -translate-x-1/2 origin-left overflow-visible transition duration-300 ease-out"
            preserveAspectRatio="none"
            viewBox="0 0 100 12"
          >
            <path d="M3 8.4 C 22 4.4, 47 4.1, 97 7.2" fill="none" opacity="0.42" stroke="currentColor" stroke-linecap="round" stroke-width="4.5" />
            <path d="M5 9.2 C 29 6.2, 58 5.5, 95 8.1" fill="none" opacity="0.72" stroke="currentColor" stroke-linecap="round" stroke-width="2.4" />
          </svg>
        </a>
      </nav>

      <Link
        v-if="$page.props.auth.user"
        :href="dashboard()"
        class="relative z-10 hidden justify-self-end rounded-md border border-secondary-500/70 px-4 py-2 text-sm font-semibold text-secondary-800 transition hover:bg-secondary-500/10 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-secondary-400 sm:inline-flex dark:text-secondary-100 dark:hover:bg-secondary-500/15"
      >
        Dashboard
      </Link>
      <a
        v-else
        href="#contact"
        class="relative z-10 inline-flex justify-self-end rounded-md bg-accent-500 px-3 py-2 text-sm font-bold text-accent-50 shadow-lg shadow-accent-500/25 transition hover:bg-accent-400 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent-100 sm:px-4"
      >
        Map a workflow
        <UIcon aria-hidden="true" class="ml-2 size-4" name="i-ph-arrow-circle-right" />
      </a>
    </div>

    <svg aria-hidden="true" class="pointer-events-none absolute size-0" focusable="false">
      <filter id="nav-glass-distortion" filterUnits="objectBoundingBox" height="100%" width="100%" x="0%" y="0%">
        <feTurbulence baseFrequency="0.01 0.01" numOctaves="1" result="turbulence" seed="5" type="fractalNoise" />
        <feComponentTransfer in="turbulence" result="mapped">
          <feFuncR amplitude="1" exponent="10" offset="0.5" type="gamma" />
          <feFuncG amplitude="0" exponent="1" offset="0" type="gamma" />
          <feFuncB amplitude="0" exponent="1" offset="0.5" type="gamma" />
        </feComponentTransfer>
        <feGaussianBlur in="turbulence" result="softMap" stdDeviation="3" />
        <feSpecularLighting in="softMap" lighting-color="white" result="specLight" specularConstant="1" specularExponent="100" surfaceScale="5">
          <fePointLight x="-200" y="-200" z="300" />
        </feSpecularLighting>
        <feComposite in="specLight" in2="" k1="0" k2="1" k3="1" k4="0" operator="arithmetic" result="litImage" />
        <feDisplacementMap in="SourceGraphic" in2="softMap" scale="90" xChannelSelector="R" yChannelSelector="G" />
      </filter>
    </svg>
  </Motion>
</template>

<style scoped>
  .nav-liquid-glass-effect {
    backdrop-filter: blur(3px) saturate(160%);
    filter: url("#nav-glass-distortion");
    -webkit-backdrop-filter: blur(3px) saturate(160%);
  }

  .nav-liquid-glass-shine {
    box-shadow:
      inset 0 0 0 1px color-mix(in oklab, var(--color-muted-100) 14%, transparent),
      inset 0 1px 0 color-mix(in oklab, var(--color-muted-50) 18%, transparent),
      inset 0 0 18px color-mix(in oklab, var(--color-muted-100) 8%, transparent);
  }

  :global(.dark) .nav-liquid-glass-shine {
    box-shadow:
      inset 0 0 0 1px color-mix(in oklab, var(--color-muted-700) 22%, transparent),
      inset 0 1px 0 color-mix(in oklab, var(--color-muted-300) 8%, transparent),
      inset 0 0 18px color-mix(in oklab, var(--color-muted-700) 16%, transparent);
  }
</style>
