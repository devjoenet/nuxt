<script lang="ts" setup>
  import FormInput from "@/components/FormInput.vue";
  import FormCurrencyInput from "@/components/FormCurrencyInput.vue";
  import FormMultiSelect from "@/components/FormMultiSelect.vue";
  import FormPhoneInput from "@/components/FormPhoneInput.vue";
  import FormSelect from "@/components/FormSelect.vue";
  import FormTextarea from "@/components/FormTextarea.vue";
  import PrivacyDialogLink from "@/components/privacy/PrivacyDialogLink.vue";
  import { store } from "@/routes/contact";
  import { Form, usePage } from "@inertiajs/vue3";
  import { nextTick, onMounted, reactive, ref } from "vue";
  import { Motion } from "motion-v";
  import type { AppPageProps } from "@/types";

  const page = usePage<AppPageProps>();

  const attribution = reactive({
    landingPage: "",
    referrer: "",
    utmSource: "",
    utmMedium: "",
    utmCampaign: "",
    utmTerm: "",
    utmContent: "",
  });

  const budgetRange = ref<number | null>(null);
  const budgetOptions = { valueRange: { min: 0 } };
  const phoneNumber = ref("");
  const successMessage = ref<HTMLElement | null>(null);

  const resetCustomFields = (): void => {
    budgetRange.value = null;
    phoneNumber.value = "";
  };

  const handleSuccess = async (): Promise<void> => {
    resetCustomFields();
    await nextTick();
    successMessage.value?.focus();
  };

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
</script>

<template>
  <Motion
    as="div"
    :initial="{ opacity: 0, x: 24 }"
    :transition="{ duration: 0.7, delay: 0.16, ease: 'easeOut' }"
    :viewport="{ once: true, amount: 0.2 }"
    :while-in-view="{ opacity: 1, x: 0 }"
  >
    <Form #default="{ errors, processing, wasSuccessful }" aria-label="Project inquiry form" class="grid gap-5" reset-on-success v-bind="store.form()" @success="handleSuccess">
      <div
        v-if="wasSuccessful || page.props.flash.intakeSuccess"
        ref="successMessage"
        aria-live="polite"
        class="rounded-lg border border-success-500/30 bg-success-500/10 p-5 text-success-800 dark:text-success-200"
        role="status"
        tabindex="-1"
      >
        <p class="font-semibold">Your message was sent successfully.</p>
        <p class="mt-1 text-sm">Thanks — we received your inquiry and will follow up shortly.</p>
      </div>

      <div class="grid gap-5 sm:grid-cols-2">
        <FormInput id="intake-name" :error="errors.name" autocomplete="name" label="Name" name="name" required variant="filled" />
        <FormInput id="intake-email" :error="errors.email" autocomplete="email" label="Email" name="email" required type="email" variant="filled" />
        <FormInput id="intake-company" :error="errors.company" autocomplete="organization" label="Organization (optional)" name="company" variant="filled" />
        <FormPhoneInput id="intake-phone" v-model="phoneNumber" :error="errors.phone" autocomplete="tel" label="Phone (optional)" name="phone" variant="filled" />
        <FormInput id="intake-website" :error="errors.website" autocomplete="url" label="Website (optional)" name="website" type="url" variant="filled" />
        <FormMultiSelect
          id="intake-service"
          :error="errors.service_interest"
          :items="[
            { label: 'Operations application', value: 'operations-app' },
            { label: 'Dashboard or portal', value: 'dashboard-portal' },
            { label: 'System integration', value: 'integration' },
            { label: 'Reporting and visibility', value: 'reporting' },
            { label: 'Legacy modernization', value: 'modernization' },
            { label: 'Other', value: 'other' },
          ]"
          label="Service interests (optional)"
          name="service_interest"
          variant="filled"
        />
        <FormCurrencyInput
          id="intake-budget"
          :error="errors.budget_range"
          v-model="budgetRange"
          :auto-emit="false"
          :options="budgetOptions"
          label="Estimated budget (optional)"
          name="budget_range"
          variant="filled"
          autocomplete="off"
        />
        <FormSelect
          id="intake-timeline"
          :error="errors.timeline"
          :items="[
            { label: 'Urgent', value: 'urgent' },
            { label: '1–3 months', value: '1-3-months' },
            { label: '3–6 months', value: '3-6-months' },
            { label: 'Exploring options', value: 'exploring' },
          ]"
          label="Timeline (optional)"
          name="timeline"
          variant="filled"
        />
      </div>

      <FormTextarea
        id="intake-details"
        :error="errors.project_details"
        :rows="6"
        label="What workflow or system needs attention?"
        maxlength="5000"
        minlength="20"
        name="project_details"
        required
        variant="filled"
      />

      <FormInput id="intake-referral" :error="errors.referral_source" label="How did you hear about us? (optional)" name="referral_source" variant="filled" />

      <div aria-hidden="true" class="sr-only">
        <label for="website-confirmation">Leave this field empty</label>
        <input id="website-confirmation" autocomplete="off" name="website_confirmation" tabindex="-1" type="text" />
      </div>

      <input :value="attribution.landingPage" name="landing_page" type="hidden" />
      <input :value="attribution.referrer" name="referrer" type="hidden" />
      <input :value="attribution.utmSource" name="utm_source" type="hidden" />
      <input :value="attribution.utmMedium" name="utm_medium" type="hidden" />
      <input :value="attribution.utmCampaign" name="utm_campaign" type="hidden" />
      <input :value="attribution.utmTerm" name="utm_term" type="hidden" />
      <input :value="attribution.utmContent" name="utm_content" type="hidden" />

      <div class="flex items-start gap-3 text-sm leading-6 text-muted-700 dark:text-neutral-300">
        <input id="intake-consent" class="mt-1 size-4 rounded border-muted-400 text-primary-600 focus:ring-primary-400" name="consent" required type="checkbox" value="1" />
        <p>
          <label for="intake-consent">I agree that Southeast Code may use these details to respond to my inquiry.</label> See the
          <PrivacyDialogLink id="intake-privacy-notice" class="font-semibold text-secondary-700 underline hover:text-secondary-600 dark:text-secondary-300"
            >privacy notice</PrivacyDialogLink
          >.
        </p>
      </div>
      <p v-if="errors.consent" class="px-1 text-base text-error sm:text-sm" role="status">{{ errors.consent }}</p>

      <button
        :disabled="processing"
        class="inline-flex min-h-12 items-center justify-center rounded-md bg-accent-500 px-6 py-3 text-sm font-bold text-accent-50 shadow-lg shadow-accent-500/25 transition hover:bg-accent-400 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent-200 disabled:cursor-wait disabled:opacity-60"
        type="submit"
      >
        {{ processing ? "Sending…" : "Send project details" }}
        <UIcon v-if="!processing" aria-hidden="true" class="ml-2 size-4" name="i-ph-arrow-circle-right" />
      </button>
    </Form>
  </Motion>
</template>
