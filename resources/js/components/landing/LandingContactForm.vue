<script lang="ts" setup>
  import { privacy } from "@/routes";
  import { store } from "@/routes/contact";
  import { Form, Link } from "@inertiajs/vue3";
  import { onMounted, reactive } from "vue";

  const attribution = reactive({
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
</script>

<template>
  <Form #default="{ errors, processing, wasSuccessful }" aria-label="Project inquiry form" class="grid gap-5" reset-on-success v-bind="store.form()">
    <div v-if="wasSuccessful" class="rounded-lg border border-success-500/30 bg-success-500/10 p-5 text-success-800 dark:text-success-200" role="status" tabindex="-1">
      <p class="font-semibold">Thanks. Your inquiry is in.</p>
      <p class="mt-1 text-sm">We’ll review the workflow details and follow up shortly.</p>
    </div>

    <div class="grid gap-5 sm:grid-cols-2">
      <div class="field">
        <input id="intake-name" autocomplete="name" name="name" placeholder=" " required type="text" />
        <label for="intake-name">Name</label>
        <p v-if="errors.name" class="field-error">{{ errors.name }}</p>
      </div>
      <div class="field">
        <input id="intake-email" autocomplete="email" name="email" placeholder=" " required type="email" />
        <label for="intake-email">Email</label>
        <p v-if="errors.email" class="field-error">{{ errors.email }}</p>
      </div>
      <div class="field">
        <input id="intake-company" autocomplete="organization" name="company" placeholder=" " type="text" />
        <label for="intake-company">Organization (optional)</label>
        <p v-if="errors.company" class="field-error">{{ errors.company }}</p>
      </div>
      <div class="field">
        <input id="intake-phone" autocomplete="tel" name="phone" placeholder=" " type="tel" />
        <label for="intake-phone">Phone (optional)</label>
        <p v-if="errors.phone" class="field-error">{{ errors.phone }}</p>
      </div>
      <div class="field">
        <input id="intake-website" autocomplete="url" name="website" placeholder=" " type="url" />
        <label for="intake-website">Website (optional)</label>
        <p v-if="errors.website" class="field-error">{{ errors.website }}</p>
      </div>
      <div class="field select-field">
        <select id="intake-service" name="service_interest">
          <option value="">Choose a service</option>
          <option value="operations-app">Operations application</option>
          <option value="dashboard-portal">Dashboard or portal</option>
          <option value="integration">System integration</option>
          <option value="reporting">Reporting and visibility</option>
          <option value="modernization">Legacy modernization</option>
          <option value="other">Other</option>
        </select>
        <label for="intake-service">Service interest (optional)</label>
        <p v-if="errors.service_interest" class="field-error">{{ errors.service_interest }}</p>
      </div>
      <div class="field select-field">
        <select id="intake-budget" name="budget_range">
          <option value="">Choose a budget range</option>
          <option value="under-10k">Under $10k</option>
          <option value="10k-25k">$10k–$25k</option>
          <option value="25k-50k">$25k–$50k</option>
          <option value="50k-plus">$50k+</option>
          <option value="not-sure">Not sure yet</option>
        </select>
        <label for="intake-budget">Budget (optional)</label>
        <p v-if="errors.budget_range" class="field-error">{{ errors.budget_range }}</p>
      </div>
      <div class="field select-field">
        <select id="intake-timeline" name="timeline">
          <option value="">Choose a timeline</option>
          <option value="urgent">Urgent</option>
          <option value="1-3-months">1–3 months</option>
          <option value="3-6-months">3–6 months</option>
          <option value="exploring">Exploring options</option>
        </select>
        <label for="intake-timeline">Timeline (optional)</label>
        <p v-if="errors.timeline" class="field-error">{{ errors.timeline }}</p>
      </div>
    </div>

    <div class="field">
      <textarea id="intake-details" maxlength="5000" minlength="20" name="project_details" placeholder=" " required rows="6"></textarea>
      <label for="intake-details">What workflow or system needs attention?</label>
      <p v-if="errors.project_details" class="field-error">{{ errors.project_details }}</p>
    </div>

    <div class="field">
      <input id="intake-referral" name="referral_source" placeholder=" " type="text" />
      <label for="intake-referral">How did you hear about us? (optional)</label>
      <p v-if="errors.referral_source" class="field-error">{{ errors.referral_source }}</p>
    </div>

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
        <Link :href="privacy()" class="font-semibold text-secondary-700 underline hover:text-secondary-600 dark:text-secondary-300">privacy notice</Link>.
      </p>
    </div>
    <p v-if="errors.consent" class="field-error">{{ errors.consent }}</p>

    <button
      :disabled="processing"
      class="inline-flex min-h-12 items-center justify-center rounded-md bg-accent-500 px-6 py-3 text-sm font-bold text-accent-50 shadow-lg shadow-accent-500/25 transition hover:bg-accent-400 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent-200 disabled:cursor-wait disabled:opacity-60"
      type="submit"
    >
      {{ processing ? "Sending…" : "Send project details" }}
      <UIcon v-if="!processing" aria-hidden="true" class="ml-2 size-4" name="i-ph-arrow-circle-right" />
    </button>
  </Form>
</template>

<style scoped>
  .field {
    position: relative;
  }
  .field input,
  .field textarea,
  .field select {
    width: 100%;
    border: 1px solid color-mix(in oklab, var(--color-muted-500) 55%, transparent);
    border-radius: 0.5rem;
    background: color-mix(in oklab, var(--color-muted-50) 92%, transparent);
    padding: 1.25rem 0.9rem 0.65rem;
    color: var(--color-muted-950);
    outline: none;
    transition:
      border-color 180ms,
      box-shadow 180ms,
      background-color 180ms;
  }
  .field textarea {
    resize: vertical;
    min-height: 9rem;
  }
  .field select {
    min-height: 3.65rem;
  }
  .field label {
    position: absolute;
    top: 1rem;
    left: 0.9rem;
    color: var(--color-muted-600);
    pointer-events: none;
    transform-origin: left top;
    transition:
      transform 160ms,
      color 160ms;
  }
  .field input:focus,
  .field textarea:focus,
  .field select:focus {
    border-color: var(--color-primary-600);
    box-shadow: 0 0 0 3px color-mix(in oklab, var(--color-primary-500) 22%, transparent);
  }
  .field input:focus + label,
  .field input:not(:placeholder-shown) + label,
  .field textarea:focus + label,
  .field textarea:not(:placeholder-shown) + label,
  .select-field label {
    transform: translateY(-0.62rem) scale(0.75);
    color: var(--color-primary-700);
  }
  .field-error {
    margin-top: 0.4rem;
    color: var(--color-error-700);
    font-size: 0.8rem;
  }
  :global(.dark) .field input,
  :global(.dark) .field textarea,
  :global(.dark) .field select {
    background: color-mix(in oklab, var(--color-muted-900) 88%, transparent);
    color: var(--color-neutral-100);
    border-color: color-mix(in oklab, var(--color-muted-300) 35%, transparent);
  }
  :global(.dark) .field label {
    color: var(--color-neutral-400);
  }
  :global(.dark) .field input:focus + label,
  :global(.dark) .field textarea:focus + label,
  :global(.dark) .select-field label {
    color: var(--color-primary-300);
  }
  :global(.dark) .field-error {
    color: var(--color-error-300);
  }
</style>
