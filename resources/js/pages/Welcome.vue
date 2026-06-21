<script setup lang="ts">
  import heroImageDark from "../../img/savannah-river-dark-mode.png";
  import heroImageLight from "../../img/savannah-river-light-mode.png";
  import FeatureCard from "@/components/landing/FeatureCard.vue";
  import LandingFooter from "@/components/landing/LandingFooter.vue";
  import LandingHeader from "@/components/landing/LandingHeader.vue";
  import LandingHero from "@/components/landing/LandingHero.vue";
  import MetricCard from "@/components/landing/MetricCard.vue";
  import OperationsShowcase from "@/components/landing/OperationsShowcase.vue";
  import ProcessTimeline from "@/components/landing/ProcessTimeline.vue";
  import SectionHeading from "@/components/landing/SectionHeading.vue";
  import { Head } from "@inertiajs/vue3";
  import { useColorMode, useElementBounding, useMounted, useWindowSize } from "@vueuse/core";
  import { computed, ref } from "vue";

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

  interface MetricItem {
    value: string;
    label: string;
    description: string;
    icon: string;
    tone: "primary" | "accent" | "secondary";
  }

  interface FooterLink {
    label: string;
    href: string;
  }

  interface FooterColumn {
    title: string;
    links: FooterLink[];
  }

  const colorMode = useColorMode();
  const heroImage = computed(() => (colorMode.state.value === "dark" ? heroImageDark : heroImageLight));
  const realitySection = ref<HTMLElement | null>(null);
  const isMounted = useMounted();
  const { top: realityTop } = useElementBounding(realitySection);
  const { height: windowHeight } = useWindowSize();

  const isRealityLiquidActive = computed(() => isMounted.value && realityTop.value <= windowHeight.value * 0.65);

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

  const realityCardDelay = (index: number): number => {
    const delayStep = 420;
    const cascadeIndex = isRealityLiquidActive.value ? index : realityCards.length - 1 - index;

    return cascadeIndex * delayStep;
  };

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

  const metrics: MetricItem[] = [
    {
      value: "24-60",
      label: "hours",
      description: "Recovered per week across operations teams",
      icon: "i-ph-clock",
      tone: "primary",
    },
    {
      value: "40%",
      label: "faster",
      description: "Approvals with automated routing and visibility",
      icon: "i-ph-lightning",
      tone: "secondary",
    },
    {
      value: "98%",
      label: "cleaner",
      description: "Handoffs with standard workflows and tracking",
      icon: "i-ph-arrows-left-right",
      tone: "accent",
    },
    {
      value: "100%",
      label: "visibility",
      description: "Into exceptions, delays, and performance",
      icon: "i-ph-eye",
      tone: "primary",
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
        { label: "About", href: "#impact" },
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
  </Head>

  <div class="min-h-screen overflow-hidden bg-muted-50 text-muted-800 selection:bg-primary-500 selection:text-primary-950 dark:bg-muted-950 dark:text-neutral-100">
    <LandingHeader />
    <main>
      <LandingHero :hero-image-src="heroImage" />
      <section id="reality" ref="realitySection" class="scroll-mt-28 border-y border-accent-500/15 bg-muted-100/90 px-5 py-16 sm:px-8 lg:py-20 dark:bg-muted-950/95">
        <div class="mx-auto max-w-7xl">
          <SectionHeading kicker="The reality" title="The workarounds are costing more than time." tone="accent" />

          <ul class="mt-8 grid gap-5 md:grid-cols-3">
            <li v-for="(card, index) in realityCards" :key="card.title">
              <FeatureCard :item="card" :liquid-active="isRealityLiquidActive" :liquid-delay="realityCardDelay(index)" centered liquid />
            </li>
          </ul>
        </div>
      </section>

      <section
        id="transformation"
        class="scroll-mt-28 bg-[radial-gradient(circle_at_center,rgba(110,104,182,0.12),transparent_34rem)] px-5 py-16 sm:px-8 lg:py-20 dark:bg-[radial-gradient(circle_at_center,rgba(110,104,182,0.14),transparent_34rem)]"
      >
        <div class="mx-auto max-w-7xl">
          <SectionHeading kicker="The transformation" title="From scattered data to operational control." tone="secondary" />

          <ol class="mt-8 grid gap-5 lg:grid-cols-3">
            <li v-for="card in transformationCards" :key="card.title" class="relative">
              <FeatureCard :item="card" />
            </li>
          </ol>
        </div>
      </section>

      <section id="services" class="scroll-mt-28 px-5 py-16 sm:px-8 lg:py-20">
        <div class="mx-auto max-w-7xl">
          <SectionHeading kicker="Our services" title="Software shaped around the operation." tone="primary" />

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
          class="absolute inset-0 bg-[radial-gradient(circle_at_18%_10%,rgba(110,104,182,0.18),transparent_26rem),radial-gradient(circle_at_82%_55%,rgba(208,57,120,0.14),transparent_24rem)] dark:bg-[radial-gradient(circle_at_18%_10%,rgba(110,104,182,0.16),transparent_26rem),radial-gradient(circle_at_82%_55%,rgba(208,57,120,0.12),transparent_24rem)]"
          aria-hidden="true"
        ></div>

        <div class="relative mx-auto grid max-w-7xl gap-10 lg:grid-cols-[0.65fr_1.35fr] lg:items-center">
          <div>
            <p class="text-xs font-bold tracking-[0.22em] text-secondary-400 uppercase">Built for operations</p>
            <h2 class="mt-4 max-w-lg text-3xl leading-tight font-semibold text-muted-950 sm:text-4xl dark:text-neutral-50">Interfaces your team can actually use.</h2>
            <p class="mt-4 max-w-md text-base leading-7 text-muted-700 dark:text-neutral-300">Clean, practical, and built for the way operations move.</p>
            <a
              href="#services"
              class="mt-8 inline-flex items-center justify-center rounded-md border border-secondary-500/80 px-5 py-3 text-sm font-semibold text-secondary-800 transition hover:border-secondary-500 hover:bg-secondary-500/10 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-secondary-400 dark:text-secondary-100 dark:hover:border-secondary-400 dark:hover:bg-secondary-500/15"
            >
              See more examples
              <UIcon aria-hidden="true" class="ml-2 size-4" name="i-ph-arrow-circle-right" />
            </a>
          </div>

          <OperationsShowcase :hero-image-src="heroImage" />
        </div>
      </section>

      <section id="impact" class="scroll-mt-28 px-5 py-16 sm:px-8 lg:py-20">
        <div class="mx-auto grid max-w-7xl gap-8 lg:grid-cols-[0.7fr_1.3fr] lg:items-center">
          <div>
            <p class="text-xs font-bold tracking-[0.22em] text-accent-400 uppercase">The impact</p>
            <h2 class="mt-4 text-3xl leading-tight font-semibold text-muted-950 sm:text-4xl dark:text-neutral-50">
              Measurable impact.
              <span class="block">Real operational wins.</span>
            </h2>
          </div>

          <ul class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
            <li v-for="metric in metrics" :key="metric.label">
              <MetricCard :metric="metric" />
            </li>
          </ul>
        </div>
      </section>

      <section
        id="contact"
        class="scroll-mt-28 border-y border-accent-500/15 bg-[radial-gradient(circle_at_center,rgba(208,57,120,0.13),transparent_38rem),radial-gradient(circle_at_78%_50%,rgba(110,104,182,0.13),transparent_26rem)] px-5 py-10 sm:px-8 dark:bg-[radial-gradient(circle_at_center,rgba(208,57,120,0.16),transparent_38rem),radial-gradient(circle_at_78%_50%,rgba(110,104,182,0.16),transparent_26rem)]"
      >
        <div
          class="mx-auto flex max-w-5xl flex-col gap-6 rounded-lg border border-accent-500/25 bg-muted-50/85 p-6 shadow-2xl shadow-accent-950/10 ring-1 ring-muted-200/70 sm:p-8 lg:flex-row lg:items-center lg:justify-between dark:bg-muted-900/80 dark:shadow-accent-950/30 dark:ring-transparent"
        >
          <div>
            <p class="text-sm font-semibold text-secondary-400">Ready to replace the workaround?</p>
            <h2 class="mt-2 text-2xl font-semibold text-muted-950 dark:text-neutral-50">Let's map your workflows and build software that fits.</h2>
          </div>

          <a
            href="mailto:hello@southeastcode.com?subject=Workflow%20review"
            class="inline-flex items-center justify-center rounded-md bg-accent-500 px-6 py-3 text-sm font-bold text-accent-50 shadow-lg shadow-accent-500/25 transition hover:bg-accent-400 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent-100"
          >
            Schedule a workflow review
            <UIcon aria-hidden="true" class="ml-2 size-4" name="i-ph-arrow-circle-right" />
          </a>
        </div>
      </section>
    </main>

    <LandingFooter :columns="footerColumns" />
  </div>
</template>
