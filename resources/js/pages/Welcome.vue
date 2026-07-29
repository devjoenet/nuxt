<script setup lang="ts">
  import darkHero640Avif from "../../img/landing/hero/savannah-river-dark-640.avif";
  import darkHero640Webp from "../../img/landing/hero/savannah-river-dark-640.webp";
  import darkHero960Avif from "../../img/landing/hero/savannah-river-dark-960.avif";
  import darkHero960Webp from "../../img/landing/hero/savannah-river-dark-960.webp";
  import darkHero1536Avif from "../../img/landing/hero/savannah-river-dark-1536.avif";
  import darkHero1536Webp from "../../img/landing/hero/savannah-river-dark-1536.webp";
  import lightHero640Avif from "../../img/landing/hero/savannah-river-light-640.avif";
  import lightHero640Webp from "../../img/landing/hero/savannah-river-light-640.webp";
  import lightHero960Avif from "../../img/landing/hero/savannah-river-light-960.avif";
  import lightHero960Webp from "../../img/landing/hero/savannah-river-light-960.webp";
  import lightHero1264Avif from "../../img/landing/hero/savannah-river-light-1264.avif";
  import lightHero1264Webp from "../../img/landing/hero/savannah-river-light-1264.webp";
  import socialImageAsset from "../../img/landing/social/southeast-code-social-card.avif";
  import FeatureCard from "@/components/landing/FeatureCard.vue";
  import LandingFooter from "@/components/landing/LandingFooter.vue";
  import LandingHeader from "@/components/landing/LandingHeader.vue";
  import LandingHero from "@/components/landing/LandingHero.vue";
  import LandingContactForm from "@/components/landing/LandingContactForm.vue";
  import OperationsShowcase from "@/components/landing/OperationsShowcase.vue";
  import ProcessTimeline from "@/components/landing/ProcessTimeline.vue";
  import SectionHeading from "@/components/landing/SectionHeading.vue";
  import { Head } from "@inertiajs/vue3";
  import { useColorMode } from "@vueuse/core";
  import { Motion, MotionConfig } from "motion-v";
  import { computed } from "vue";
  import type { Option } from "@/types/crm";

  const props = defineProps<{ canonicalUrl: string; serviceInterestOptions: Option[] }>();
  const socialImage = computed(() => new URL(socialImageAsset, props.canonicalUrl).href);

  interface LandingCard {
    title: string;
    description: string;
    detail: string;
    icon: string;
    tone: "primary" | "accent" | "secondary" | "warning";
  }

  interface ServiceItem extends LandingCard {
    proof: string;
  }

  interface ProcessStep {
    title: string;
    description: string;
    icon: string;
    tone: "primary" | "secondary" | "accent" | "warning";
  }

  interface FooterLink {
    label: string;
    href: string;
  }

  interface FooterColumn {
    title: string;
    links: FooterLink[];
  }

  interface HeroAsset {
    src: string;
    width: number;
    height: number;
  }

  interface HeroAssetSet {
    avif: HeroAsset[];
    webp: HeroAsset[];
    fallback: HeroAsset;
  }

  const lightHero: HeroAssetSet = {
    avif: [
      { src: lightHero640Avif, width: 640, height: 426 },
      { src: lightHero960Avif, width: 960, height: 640 },
      { src: lightHero1264Avif, width: 1264, height: 842 },
    ],
    webp: [
      { src: lightHero640Webp, width: 640, height: 427 },
      { src: lightHero960Webp, width: 960, height: 640 },
      { src: lightHero1264Webp, width: 1264, height: 842 },
    ],
    fallback: { src: lightHero1264Webp, width: 1264, height: 842 },
  };

  const darkHero: HeroAssetSet = {
    avif: [
      { src: darkHero640Avif, width: 640, height: 426 },
      { src: darkHero960Avif, width: 960, height: 640 },
      { src: darkHero1536Avif, width: 1536, height: 1024 },
    ],
    webp: [
      { src: darkHero640Webp, width: 640, height: 427 },
      { src: darkHero960Webp, width: 960, height: 640 },
      { src: darkHero1536Webp, width: 1536, height: 1024 },
    ],
    fallback: { src: darkHero1536Webp, width: 1536, height: 1024 },
  };

  const colorMode = useColorMode();
  const hero = computed(() => (colorMode.state.value === "dark" ? darkHero : lightHero));
  const heroImage = computed(() => hero.value.fallback.src);
  const heroImageAvifSrcset = computed(() => hero.value.avif.map((asset) => `${asset.src} ${asset.width}w`).join(", "));
  const heroImageWebpSrcset = computed(() => hero.value.webp.map((asset) => `${asset.src} ${asset.width}w`).join(", "));
  const realityCards: LandingCard[] = [
    {
      title: "Spreadsheet chains",
      description: "Too many versions.",
      detail: "Too many dead ends.",
      icon: "i-ph-stack",
      tone: "secondary",
    },
    {
      title: "Disconnected tools",
      description: "Data trapped in silos.",
      detail: "No single source of truth.",
      icon: "i-ph-flow-arrow",
      tone: "primary",
    },
    {
      title: "Legacy bottlenecks",
      description: "Manual workarounds.",
      detail: "Slow, risky, expensive.",
      icon: "i-ph-warning-circle",
      tone: "accent",
    },
  ];

  const transformationCards: LandingCard[] = [
    {
      title: "Centralized workflows",
      description: "One system. Clear steps.",
      detail: "Built around your process.",
      icon: "i-ph-flow-arrow",
      tone: "primary",
    },
    {
      title: "Reliable data",
      description: "Clean, real-time, and ready for decisions.",
      detail: "No guessing from stale exports.",
      icon: "i-ph-database",
      tone: "secondary",
    },
    {
      title: "Role-based access",
      description: "Right people. Right information.",
      detail: "Every time.",
      icon: "i-ph-shield-check",
      tone: "accent",
    },
  ];

  const services: ServiceItem[] = [
    {
      title: "Dashboards & portals",
      description: "Real-time visibility for teams, customers, and partners.",
      detail: "Centralized operational hubs.",
      proof: "Live work queues, status views, and approval portals.",
      icon: "i-ph-layout",
      tone: "primary",
    },
    {
      title: "Internal tools",
      description: "Custom apps that replace spreadsheets and repetitive daily operational work.",
      detail: "Built around the actual workflow.",
      proof: "Daily load plans, dock checks, and driver payouts.",
      icon: "i-ph-key",
      tone: "secondary",
    },
    {
      title: "System integration",
      description: "Connect the tools you depend on. Automate data flow. Eliminate manual re-entry.",
      detail: "APIs, files, and background jobs.",
      proof: "Shared records across the systems already in place.",
      icon: "i-ph-plugs-connected",
      tone: "primary",
    },
    {
      title: "Reporting & data visibility",
      description: "Operational reporting that answers the right questions fast.",
      detail: "Metrics people can act on.",
      proof: "Trends, exceptions, and performance snapshots.",
      icon: "i-ph-chart-bar",
      tone: "secondary",
    },
    {
      title: "Legacy modernization",
      description: "Modernize outdated systems without disrupting the business.",
      detail: "More stable. More secure.",
      proof: "Replace brittle workflows one useful slice at a time.",
      icon: "i-ph-lightning",
      tone: "warning",
    },
    {
      title: "Progressive web apps",
      description: "Reliable on any device. Works offline, installs in seconds. Always up to date.",
      detail: "Field-ready web software.",
      proof: "Mobile check-ins, photo capture, and offline queues.",
      icon: "i-ph-device-mobile",
      tone: "primary",
    },
  ];

  const processSteps: ProcessStep[] = [
    {
      title: "Observe workflows",
      description: "We learn how your teams actually work from end to end.",
      icon: "i-ph-binoculars",
      tone: "primary",
    },
    {
      title: "Model the data",
      description: "We structure the data and rules that power the process.",
      icon: "i-ph-database",
      tone: "secondary",
    },
    {
      title: "Build the tool",
      description: "We build secure, tested software that fits.",
      icon: "i-ph-hammer",
      tone: "accent",
    },
    {
      title: "Improve with evidence",
      description: "We refine based on real usage and measurable outcomes.",
      icon: "i-ph-chart-line-up",
      tone: "warning",
    },
  ];

  const footerColumns: FooterColumn[] = [
    {
      title: "Solutions",
      links: [
        { label: "Dashboards & portals", href: "#services" },
        { label: "Internal tools", href: "#services" },
        { label: "System integration", href: "#services" },
        { label: "Reporting", href: "#services" },
        { label: "Legacy modernization", href: "#services" },
        { label: "Progressive web apps", href: "#services" },
      ],
    },
    {
      title: "Company",
      links: [
        { label: "Process", href: "#process" },
        { label: "Workflows", href: "#operations" },
        { label: "Careers", href: "#contact" },
        { label: "Contact", href: "#contact" },
      ],
    },
    {
      title: "Technology",
      links: [
        { label: "Laravel", href: "#services" },
        { label: "Vue", href: "#services" },
        { label: "TypeScript", href: "#services" },
        { label: "Inertia", href: "#services" },
        { label: "MySQL", href: "#services" },
        { label: "Redis", href: "#services" },
        { label: "Tailwind CSS", href: "#services" },
      ],
    },
  ];
</script>

<template>
  <Head title="Southeast Code">
    <meta
      head-key="description"
      name="description"
      content="Southeast Code builds custom web applications for operations-heavy organizations that have outgrown spreadsheets, disconnected tools, and legacy systems."
    />
    <link :href="canonicalUrl" head-key="canonical" rel="canonical" />
    <meta content="website" head-key="og:type" property="og:type" />
    <meta content="Southeast Code | Custom operations software" head-key="og:title" property="og:title" />
    <meta
      content="Custom web applications for operations-heavy organizations that have outgrown spreadsheets and disconnected systems."
      head-key="og:description"
      property="og:description"
    />
    <meta :content="canonicalUrl" head-key="og:url" property="og:url" />
    <meta :content="socialImage" head-key="og:image" property="og:image" />
    <meta content="summary_large_image" head-key="twitter:card" name="twitter:card" />
    <meta content="Southeast Code | Custom operations software" head-key="twitter:title" name="twitter:title" />
    <meta content="Practical software built around how your operation actually works." head-key="twitter:description" name="twitter:description" />
    <meta :content="socialImage" head-key="twitter:image" name="twitter:image" />
  </Head>

  <MotionConfig reduced-motion="user">
    <div class="min-h-screen overflow-hidden bg-muted-50 text-muted-800 selection:bg-primary-500 selection:text-primary-950 dark:bg-muted-950 dark:text-neutral-100">
      <LandingHeader />
      <main>
        <LandingHero
          :hero-image-avif-srcset="heroImageAvifSrcset"
          :hero-image-height="hero.fallback.height"
          :hero-image-src="heroImage"
          :hero-image-width="hero.fallback.width"
          :hero-image-webp-srcset="heroImageWebpSrcset"
        />
        <section id="reality" class="scroll-mt-28 border-y border-accent-500/15 bg-muted-100/90 px-5 py-16 sm:px-8 lg:py-20 dark:bg-muted-950/95">
          <div class="mx-auto max-w-7xl">
            <SectionHeading :animated="false" kicker="The reality" title="The workarounds are costing more than time." tone="accent" />

            <ul class="mt-8 grid gap-5 md:grid-cols-3">
              <li v-for="card in realityCards" :key="card.title">
                <FeatureCard :item="card" centered />
              </li>
            </ul>
          </div>
        </section>

        <section
          id="transformation"
          class="scroll-mt-28 bg-[radial-gradient(circle_at_center,rgba(110,104,182,0.12),transparent_34rem)] px-5 py-16 sm:px-8 lg:py-20 dark:bg-[radial-gradient(circle_at_center,rgba(110,104,182,0.14),transparent_34rem)]"
        >
          <div class="mx-auto max-w-7xl">
            <SectionHeading :animated="false" kicker="The transformation" title="From scattered data to operational control." tone="secondary" />

            <ol class="mt-8 grid gap-5 lg:grid-cols-3">
              <li v-for="card in transformationCards" :key="card.title" class="relative">
                <FeatureCard :item="card" />
              </li>
            </ol>
          </div>
        </section>

        <section id="services" class="scroll-mt-28 px-5 py-16 sm:px-8 lg:py-20">
          <div class="mx-auto max-w-7xl">
            <SectionHeading :animated="false" kicker="Our services" title="Software shaped around the operation." tone="primary" />

            <ul class="mt-8 grid gap-5 lg:grid-cols-3">
              <li v-for="service in services" :key="service.title">
                <FeatureCard :item="service" :proof="service.proof" />
              </li>
            </ul>
          </div>
        </section>

        <section id="process" class="scroll-mt-28 px-5 py-16 sm:px-8 lg:py-20">
          <div class="mx-auto max-w-7xl">
            <SectionHeading kicker="Our process" title="Map first. Build second." tone="accent" />
            <ProcessTimeline :steps="processSteps" />
          </div>
        </section>

        <section id="operations" class="relative scroll-mt-28 border-y border-secondary-500/15 px-5 py-16 sm:px-8 lg:py-20">
          <div
            aria-hidden="true"
            class="absolute inset-0 bg-[radial-gradient(circle_at_18%_10%,rgba(110,104,182,0.18),transparent_26rem),radial-gradient(circle_at_82%_55%,rgba(208,57,120,0.14),transparent_24rem)] dark:bg-[radial-gradient(circle_at_18%_10%,rgba(110,104,182,0.16),transparent_26rem),radial-gradient(circle_at_82%_55%,rgba(208,57,120,0.12),transparent_24rem)]"
          ></div>

          <div class="relative mx-auto grid max-w-7xl gap-10 lg:grid-cols-[0.65fr_1.35fr] lg:items-center">
            <Motion
              as="div"
              :initial="{ opacity: 0, y: 24 }"
              :transition="{ duration: 0.7, ease: 'easeOut' }"
              :viewport="{ once: true, amount: 0.3 }"
              :while-in-view="{ opacity: 1, y: 0 }"
            >
              <p class="text-xs font-bold tracking-[0.22em] text-secondary-400 uppercase">Built for operations</p>
              <h2 class="mt-4 max-w-lg text-3xl leading-tight font-semibold text-muted-950 sm:text-4xl dark:text-neutral-50">Interfaces your team can actually use.</h2>
              <p class="mt-4 max-w-md text-base leading-7 text-muted-700 dark:text-neutral-300">Clean, practical, and built for the way operations move.</p>
              <a
                class="mt-8 inline-flex items-center justify-center rounded-md border border-secondary-500/80 px-5 py-3 text-sm font-semibold text-secondary-800 transition hover:border-secondary-500 hover:bg-secondary-500/10 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-secondary-400 dark:text-secondary-100 dark:hover:border-secondary-400 dark:hover:bg-secondary-500/15"
                href="#services"
              >
                See more examples
                <UIcon aria-hidden="true" class="ml-2 size-4" name="i-ph-arrow-circle-right" />
              </a>
            </Motion>

            <OperationsShowcase :hero-image-src="heroImage" />
          </div>
        </section>

        <section
          id="contact"
          class="scroll-mt-28 border-y border-accent-500/15 bg-[radial-gradient(circle_at_center,rgba(208,57,120,0.13),transparent_38rem),radial-gradient(circle_at_78%_50%,rgba(110,104,182,0.13),transparent_26rem)] px-5 py-16 sm:px-8 lg:py-20 dark:bg-[radial-gradient(circle_at_center,rgba(208,57,120,0.16),transparent_38rem),radial-gradient(circle_at_78%_50%,rgba(110,104,182,0.16),transparent_26rem)]"
        >
          <Motion
            as="div"
            :initial="{ opacity: 0, y: 30, scale: 0.98 }"
            :transition="{ duration: 0.75, delay: 0.1, ease: 'easeOut' }"
            :viewport="{ once: true, amount: 0.22 }"
            :while-in-view="{ opacity: 1, y: 0, scale: 1 }"
            class="mx-auto grid max-w-6xl gap-10 rounded-xl border border-accent-500/25 bg-muted-50/90 p-6 shadow-2xl shadow-accent-950/10 ring-1 ring-muted-200/70 sm:p-8 lg:grid-cols-[0.72fr_1.28fr] lg:p-10 dark:bg-muted-900/85 dark:shadow-accent-950/30 dark:ring-transparent"
          >
            <div>
              <p class="text-sm font-semibold text-secondary-600 dark:text-secondary-300">Ready to replace the workaround?</p>
              <h2 class="mt-3 text-3xl leading-tight font-semibold text-muted-950 dark:text-neutral-50">Let’s map the workflow before anyone writes code.</h2>
              <p class="mt-4 leading-7 text-muted-700 dark:text-neutral-300">
                Tell us where the process breaks down, what your team is doing manually, and what a useful result looks like. Specifics beat polished sales language every time.
              </p>
              <ul class="mt-6 space-y-3 text-sm text-muted-700 dark:text-neutral-300">
                <li class="flex gap-3"><UIcon class="mt-0.5 size-5 shrink-0 text-primary-600 dark:text-primary-300" name="i-ph-check-circle" />No generic package pitch</li>
                <li class="flex gap-3"><UIcon class="mt-0.5 size-5 shrink-0 text-primary-600 dark:text-primary-300" name="i-ph-check-circle" />Practical technical review</li>
                <li class="flex gap-3">
                  <UIcon class="mt-0.5 size-5 shrink-0 text-primary-600 dark:text-primary-300" name="i-ph-check-circle" />Clear next step, even if it is “not yet”
                </li>
              </ul>
            </div>
            <LandingContactForm :service-interest-options="serviceInterestOptions" />
          </Motion>
        </section>
      </main>

      <LandingFooter :columns="footerColumns" />
    </div>
  </MotionConfig>
</template>
