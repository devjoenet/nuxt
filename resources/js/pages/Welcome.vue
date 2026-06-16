<script setup lang="ts">
  import heroImage from "@/assets/landing/savannah-riverfront-hero.png";
  import logoImage from "@/assets/landing/southeast-code-logo.png";
  import FeatureCard from "@/components/landing/FeatureCard.vue";
  import LandingFooter from "@/components/landing/LandingFooter.vue";
  import LandingHeader from "@/components/landing/LandingHeader.vue";
  import LandingHero from "@/components/landing/LandingHero.vue";
  import MetricCard from "@/components/landing/MetricCard.vue";
  import OperationsShowcase from "@/components/landing/OperationsShowcase.vue";
  import ProcessTimeline from "@/components/landing/ProcessTimeline.vue";
  import SectionHeading from "@/components/landing/SectionHeading.vue";
  import { Head } from "@inertiajs/vue3";
  import {
    ArrowLeftRight,
    BarChart3,
    Boxes,
    CircleAlert,
    Clock3,
    Database,
    Eye,
    KeyRound,
    LayoutDashboard,
    PlugZap,
    ShieldCheck,
    Smartphone,
    Workflow,
    Zap,
  } from "lucide-vue-next";
  import type { Component } from "vue";

  interface LandingCard {
    title: string;
    description: string;
    detail: string;
    icon: Component;
    tone: "primary" | "accent" | "secondary" | "warning";
  }

  interface ServiceItem extends LandingCard {
    proof: string;
  }

  interface ProcessStep {
    title: string;
    description: string;
    tone: "primary" | "secondary" | "accent" | "warning";
  }

  interface MetricItem {
    value: string;
    label: string;
    description: string;
    icon: Component;
    tone: "primary" | "accent";
  }

  interface FooterLink {
    label: string;
    href: string;
  }

  interface FooterColumn {
    title: string;
    links: FooterLink[];
  }

  const realityCards: LandingCard[] = [
    {
      title: "Spreadsheet chains",
      description: "Too many versions.",
      detail: "Too many dead ends.",
      icon: Boxes,
      tone: "accent",
    },
    {
      title: "Disconnected tools",
      description: "Data trapped in silos.",
      detail: "No single source of truth.",
      icon: Workflow,
      tone: "accent",
    },
    {
      title: "Legacy bottlenecks",
      description: "Manual workarounds.",
      detail: "Slow, risky, expensive.",
      icon: CircleAlert,
      tone: "accent",
    },
  ];

  const transformationCards: LandingCard[] = [
    {
      title: "Centralized workflows",
      description: "One system. Clear steps.",
      detail: "Built around your process.",
      icon: Workflow,
      tone: "primary",
    },
    {
      title: "Reliable data",
      description: "Clean, real-time, and ready for decisions.",
      detail: "No guessing from stale exports.",
      icon: Database,
      tone: "primary",
    },
    {
      title: "Role-based access",
      description: "Right people. Right information.",
      detail: "Every time.",
      icon: ShieldCheck,
      tone: "primary",
    },
  ];

  const services: ServiceItem[] = [
    {
      title: "Dashboards & portals",
      description: "Real-time visibility for teams, customers, and partners.",
      detail: "Centralized operational hubs.",
      proof: "Live work queues, status views, and approval portals.",
      icon: LayoutDashboard,
      tone: "primary",
    },
    {
      title: "Internal tools",
      description: "Custom apps that replace spreadsheets and repetitive daily operational work.",
      detail: "Built around the actual workflow.",
      proof: "Daily load plans, dock checks, and driver payouts.",
      icon: KeyRound,
      tone: "secondary",
    },
    {
      title: "System integration",
      description: "Connect the tools you depend on. Automate data flow. Eliminate manual re-entry.",
      detail: "APIs, files, and background jobs.",
      proof: "Shared records across the systems already in place.",
      icon: PlugZap,
      tone: "primary",
    },
    {
      title: "Reporting & data visibility",
      description: "Operational reporting that answers the right questions fast.",
      detail: "Metrics people can act on.",
      proof: "Trends, exceptions, and performance snapshots.",
      icon: BarChart3,
      tone: "secondary",
    },
    {
      title: "Legacy modernization",
      description: "Modernize outdated systems without disrupting the business.",
      detail: "More stable. More secure.",
      proof: "Replace brittle workflows one useful slice at a time.",
      icon: Zap,
      tone: "warning",
    },
    {
      title: "Progressive web apps",
      description: "Reliable on any device. Works offline, installs in seconds. Always up to date.",
      detail: "Field-ready web software.",
      proof: "Mobile check-ins, photo capture, and offline queues.",
      icon: Smartphone,
      tone: "primary",
    },
  ];

  const processSteps: ProcessStep[] = [
    {
      title: "Observe workflows",
      description: "We learn how your teams actually work from end to end.",
      tone: "primary",
    },
    {
      title: "Model the data",
      description: "We structure the data and rules that power the process.",
      tone: "secondary",
    },
    {
      title: "Build the tool",
      description: "We build secure, tested software that fits.",
      tone: "accent",
    },
    {
      title: "Improve with evidence",
      description: "We refine based on real usage and measurable outcomes.",
      tone: "warning",
    },
  ];

  const metrics: MetricItem[] = [
    {
      value: "24-60",
      label: "hours",
      description: "Recovered per week across operations teams",
      icon: Clock3,
      tone: "primary",
    },
    {
      value: "40%",
      label: "faster",
      description: "Approvals with automated routing and visibility",
      icon: Zap,
      tone: "accent",
    },
    {
      value: "98%",
      label: "cleaner",
      description: "Handoffs with standard workflows and tracking",
      icon: ArrowLeftRight,
      tone: "primary",
    },
    {
      value: "100%",
      label: "visibility",
      description: "Into exceptions, delays, and performance",
      icon: Eye,
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

  <div class="min-h-screen overflow-hidden bg-muted-950 text-neutral-100 selection:bg-brand-primary-500 selection:text-muted-950">
    <LandingHeader :logo-src="logoImage" />

    <main>
      <LandingHero :hero-image-src="heroImage" />

      <section id="reality" class="border-y border-brand-primary-500/10 bg-muted-950/95 px-5 py-16 sm:px-8 lg:py-20">
        <div class="mx-auto max-w-7xl">
          <SectionHeading kicker="The reality" title="The workarounds are costing more than time." />

          <ul class="mt-8 grid gap-5 md:grid-cols-3">
            <li v-for="card in realityCards" :key="card.title">
              <FeatureCard :item="card" centered />
            </li>
          </ul>
        </div>
      </section>

      <section id="transformation" class="bg-[radial-gradient(circle_at_center,rgba(54,243,225,0.08),transparent_34rem)] px-5 py-16 sm:px-8 lg:py-20">
        <div class="mx-auto max-w-7xl">
          <SectionHeading kicker="The transformation" title="From scattered data to operational control." />

          <ol class="mt-8 grid gap-5 lg:grid-cols-3">
            <li v-for="card in transformationCards" :key="card.title" class="relative">
              <FeatureCard :item="card" />
            </li>
          </ol>
        </div>
      </section>

      <section id="services" class="px-5 py-16 sm:px-8 lg:py-20">
        <div class="mx-auto max-w-7xl">
          <SectionHeading kicker="Our services" title="Software shaped around the operation." />

          <ul class="mt-8 grid gap-5 lg:grid-cols-3">
            <li v-for="service in services" :key="service.title">
              <FeatureCard :item="service" :proof="service.proof" />
            </li>
          </ul>
        </div>
      </section>

      <section id="process" class="px-5 py-16 sm:px-8 lg:py-20">
        <div class="mx-auto max-w-7xl">
          <SectionHeading kicker="Our process" title="Map first. Build second." />
          <ProcessTimeline :steps="processSteps" />
        </div>
      </section>

      <section id="operations" class="relative border-y border-brand-primary-500/10 px-5 py-16 sm:px-8 lg:py-20">
        <div
          class="absolute inset-0 bg-[radial-gradient(circle_at_18%_10%,rgba(10,180,237,0.12),transparent_26rem),radial-gradient(circle_at_82%_55%,rgba(54,243,225,0.09),transparent_24rem)]"
          aria-hidden="true"
        ></div>

        <div class="relative mx-auto grid max-w-7xl gap-10 lg:grid-cols-[0.65fr_1.35fr] lg:items-center">
          <div>
            <p class="text-xs font-bold tracking-[0.22em] text-brand-primary-400 uppercase">Built for operations</p>
            <h2 class="mt-4 max-w-lg text-3xl leading-tight font-semibold text-neutral-50 sm:text-4xl">Interfaces your team can actually use.</h2>
            <p class="mt-4 max-w-md text-base leading-7 text-neutral-300">Clean, practical, and built for the way operations move.</p>
            <a
              href="#services"
              class="mt-8 inline-flex items-center justify-center rounded-md border border-brand-primary-400/80 px-5 py-3 text-sm font-semibold text-brand-primary-200 transition hover:border-brand-primary-300 hover:bg-brand-primary-400/10 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-primary-300"
            >
              See more examples
              <span aria-hidden="true" class="ml-2">-></span>
            </a>
          </div>

          <OperationsShowcase :hero-image-src="heroImage" />
        </div>
      </section>

      <section id="impact" class="px-5 py-16 sm:px-8 lg:py-20">
        <div class="mx-auto grid max-w-7xl gap-8 lg:grid-cols-[0.7fr_1.3fr] lg:items-center">
          <div>
            <p class="text-xs font-bold tracking-[0.22em] text-brand-primary-400 uppercase">The impact</p>
            <h2 class="mt-4 text-3xl leading-tight font-semibold text-neutral-50 sm:text-4xl">
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

      <section id="contact" class="border-y border-brand-primary-500/10 bg-[radial-gradient(circle_at_center,rgba(54,243,225,0.12),transparent_38rem)] px-5 py-10 sm:px-8">
        <div
          class="mx-auto flex max-w-5xl flex-col gap-6 rounded-lg border border-brand-primary-400/20 bg-muted-900/80 p-6 shadow-2xl shadow-brand-primary-950/30 sm:p-8 lg:flex-row lg:items-center lg:justify-between"
        >
          <div>
            <p class="text-sm font-semibold text-brand-primary-300">Ready to replace the workaround?</p>
            <h2 class="mt-2 text-2xl font-semibold text-neutral-50">Let's map your workflows and build software that fits.</h2>
          </div>

          <a
            href="mailto:hello@southeastcode.com?subject=Workflow%20review"
            class="inline-flex items-center justify-center rounded-md bg-brand-primary-500 px-6 py-3 text-sm font-bold text-muted-950 shadow-lg shadow-brand-primary-500/20 transition hover:bg-brand-primary-400 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-primary-200"
          >
            Schedule a workflow review
            <span aria-hidden="true" class="ml-2">-></span>
          </a>
        </div>
      </section>
    </main>

    <LandingFooter :logo-src="logoImage" :columns="footerColumns" />
  </div>
</template>
