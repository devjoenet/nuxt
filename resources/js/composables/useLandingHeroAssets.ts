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

export interface HeroAsset {
  src: string;
  width: number;
  height: number;
}

export interface HeroAssetSet {
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

export function useLandingHeroAssets() {
  return {
    darkHero,
    lightHero,
  };
}
