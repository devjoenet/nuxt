<script setup lang="ts">
import { Head } from "@inertiajs/vue3";
import { MotionConfig } from "motion-v";
import { computed } from "vue";
import socialImageAsset from "../../img/landing/social/southeast-code-social-card.avif";
import LandingFooter from "@/components/Landing/LandingFooter.vue";
import LandingHeader from "@/components/Landing/LandingHeader.vue";
import LandingHero from "@/components/Landing/LandingHero.vue";
import { useLandingHeroAssets } from "@/composables/useLandingHeroAssets";
import LandingCardGridSection from "@/sections/Landing/LandingCardGridSection.vue";
import LandingContactSection from "@/sections/Landing/LandingContactSection.vue";
import LandingOperationsSection from "@/sections/Landing/LandingOperationsSection.vue";
import LandingProcessSection from "@/sections/Landing/LandingProcessSection.vue";
import { footerColumns, processSteps, realityCards, services, transformationCards } from "@/sections/Landing/landingContent";
import type { Appearance } from "@/types";
import type { Option } from "@/types/crm";

const props = defineProps<{ appearance: Appearance; canonicalUrl: string; serviceInterestOptions: Option[] }>();
const socialImage = computed(() => new URL(socialImageAsset, props.canonicalUrl).href);

const { darkHero, lightHero } = useLandingHeroAssets();
</script>

<template>
  <Head title="Southeast Code">
    <meta head-key="description" name="description" content="Southeast Code builds custom web applications for operations-heavy organizations that have outgrown spreadsheets, disconnected tools, and legacy systems." />
    <link :href="canonicalUrl" head-key="canonical" rel="canonical" />
    <meta content="website" head-key="og:type" property="og:type" />
    <meta content="Southeast Code | Custom operations software" head-key="og:title" property="og:title" />
    <meta content="Custom web applications for operations-heavy organizations that have outgrown spreadsheets and disconnected systems." head-key="og:description" property="og:description" />
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
        <LandingHero :appearance="appearance" :dark-hero="darkHero" :light-hero="lightHero" />
        <LandingCardGridSection centered class="border-y border-accent-500/15 bg-muted-100/90 dark:bg-muted-950/95" id="reality" :items="realityCards" kicker="The reality" title="The workarounds are costing more than time." tone="accent" />
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
        <LandingOperationsSection :appearance="appearance" :dark-hero="darkHero" :light-hero="lightHero" />
        <LandingContactSection :service-interest-options="serviceInterestOptions" />
      </main>
      <LandingFooter :columns="footerColumns" />
    </div>
  </MotionConfig>
</template>
