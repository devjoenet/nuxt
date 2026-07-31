<script setup lang="ts">
  import { Head } from "@inertiajs/vue3";
  import { useColorMode } from "@vueuse/core";
  import { MotionConfig } from "motion-v";
  import { computed } from "vue";
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
  import LandingFooter from "@/components/landing/LandingFooter.vue";
  import LandingHeader from "@/components/landing/LandingHeader.vue";
  import LandingHero from "@/components/landing/LandingHero.vue";
  import LandingCardGridSection from "@/sections/landing/LandingCardGridSection.vue";
  import LandingContactSection from "@/sections/landing/LandingContactSection.vue";
  import LandingOperationsSection from "@/sections/landing/LandingOperationsSection.vue";
  import LandingProcessSection from "@/sections/landing/LandingProcessSection.vue";
  import { footerColumns, processSteps, realityCards, services, transformationCards } from "@/sections/landing/landingContent";
  import type { Option } from "@/types/crm";

  const props = defineProps<{ canonicalUrl: string; serviceInterestOptions: Option[] }>();
  const socialImage = computed(() => new URL(socialImageAsset, props.canonicalUrl).href);

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
        <LandingCardGridSection
          centered
          class="border-y border-accent-500/15 bg-muted-100/90 dark:bg-muted-950/95"
          id="reality"
          :items="realityCards"
          kicker="The reality"
          title="The workarounds are costing more than time."
          tone="accent"
        />
        <LandingCardGridSection
          class="bg-[radial-gradient(circle_at_center,rgba(110,104,182,0.12),transparent_34rem)] dark:bg-[radial-gradient(circle_at_center,rgba(110,104,182,0.14),transparent_34rem)]"
          grid-class="lg:grid-cols-3"
          id="transformation"
          :items="transformationCards"
          kicker="The transformation"
          ordered
          title="From scattered data to operational control."
          tone="secondary"
        />
        <LandingCardGridSection grid-class="lg:grid-cols-3" id="services" :items="services" kicker="Our services" title="Software shaped around the operation." tone="primary" />
        <LandingProcessSection :steps="processSteps" />
        <LandingOperationsSection :hero-image-src="heroImage" />
        <LandingContactSection :service-interest-options="serviceInterestOptions" />
      </main>
      <LandingFooter :columns="footerColumns" />
    </div>
  </MotionConfig>
</template>
