<script setup lang="ts">
import type { HeroAsset, HeroAssetSet } from "./useLandingHeroAssets";
import type { Appearance } from "@/types";
import { computed } from "vue";

const props = withDefaults(
  defineProps<{
    appearance: Appearance;
    darkHero: HeroAssetSet;
    lightHero: HeroAssetSet;
    imageClass: string;
    pictureClass: string;
    fetchPriority?: "high" | "low" | "auto";
    loading?: "eager" | "lazy";
    sizes?: string;
  }>(),
  {
    loading: "eager",
    sizes: "100vw",
  },
);

const fallback = computed(() => (props.appearance === "dark" ? props.darkHero.fallback : props.lightHero.fallback));
const darkMedia = computed(() => (props.appearance === "system" ? "(prefers-color-scheme: dark)" : props.appearance === "dark" ? undefined : "not all"));
const lightMedia = computed(() => (props.appearance === "system" ? "(prefers-color-scheme: light)" : props.appearance === "light" ? undefined : "not all"));
const srcset = (assets: HeroAsset[]): string => assets.map((asset) => `${asset.src} ${asset.width}w`).join(", ");
</script>

<template>
  <picture :class="pictureClass">
    <source :media="darkMedia" :sizes="sizes" :srcset="srcset(darkHero.avif)" type="image/avif" />
    <source :media="darkMedia" :sizes="sizes" :srcset="srcset(darkHero.webp)" type="image/webp" />
    <source :media="lightMedia" :sizes="sizes" :srcset="srcset(lightHero.avif)" type="image/avif" />
    <source :media="lightMedia" :sizes="sizes" :srcset="srcset(lightHero.webp)" type="image/webp" />
    <img :class="imageClass" :fetchpriority="fetchPriority" :height="fallback.height" :loading="loading" :sizes="sizes" :src="fallback.src" :width="fallback.width" alt="" decoding="async" />
  </picture>
</template>
