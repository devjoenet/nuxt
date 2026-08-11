import { onMounted, reactive, readonly } from "vue";

export interface LandingAttribution {
  landingPage: string;
  referrer: string;
  utmSource: string;
  utmMedium: string;
  utmCampaign: string;
  utmTerm: string;
  utmContent: string;
}

export function useLandingAttribution(): Readonly<LandingAttribution> {
  const attribution = reactive<LandingAttribution>({
    landingPage: "",
    referrer: "",
    utmSource: "",
    utmMedium: "",
    utmCampaign: "",
    utmTerm: "",
    utmContent: "",
  });

  onMounted(() => {
    const search = new URLSearchParams(window.location.search);

    attribution.landingPage = window.location.pathname;
    attribution.referrer = document.referrer;
    attribution.utmSource = search.get("utm_source") ?? "";
    attribution.utmMedium = search.get("utm_medium") ?? "";
    attribution.utmCampaign = search.get("utm_campaign") ?? "";
    attribution.utmTerm = search.get("utm_term") ?? "";
    attribution.utmContent = search.get("utm_content") ?? "";
  });

  return readonly(attribution);
}
