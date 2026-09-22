<script setup lang="ts">
import { Button } from "@/components/Ui/Button";
import { Card } from "@/components/Ui/Card";
import { FormCheckbox } from "@/components/Ui/FormCheckbox";
import { FormInput } from "@/components/Ui/FormInput";
import { Icon } from "@/components/Ui/Icon";
import { computed, ref } from "vue";

type AuthenticationSpecimen = "error-404" | "error-500" | "lock" | "reset" | "sign-in" | "sign-up" | "verification";
type AuthenticationLayout = "basic" | "cover" | "illustration";

const props = defineProps<{ layout: AuthenticationLayout; specimen: AuthenticationSpecimen }>();
const email = ref("alex@example.com");
const name = ref("Alex Morgan");
const password = ref("");
const verificationCode = ref("");
const remember = ref(true);
const terms = ref(false);
const submitted = ref(false);
const isError = computed(() => props.specimen.startsWith("error-"));
const errorStatus = computed(() => props.specimen.replace("error-", ""));
const title = computed(
  () =>
    ({
      "error-404": "Page not found",
      "error-500": "Something went wrong",
      lock: "Welcome back, Alex",
      reset: "Reset password",
      "sign-in": "Welcome back",
      "sign-up": "Create your account",
      verification: "2-Step Verification",
    })[props.specimen],
);
const description = computed(
  () =>
    ({
      "error-404": "The requested gallery destination could not be found.",
      "error-500": "The preview records a recoverable server-error state.",
      lock: "Enter your password to unlock this preview.",
      reset: "We will preview the recovery state for this address.",
      "sign-in": "Use the inert credentials below to inspect this layout.",
      "sign-up": "Register a gallery-only profile without creating an account.",
      verification: "Enter the six-digit code sent to your trusted device.",
    })[props.specimen],
);
const actionLabel = computed(
  () =>
    ({
      "error-404": "Go to gallery home",
      "error-500": "Go to gallery home",
      lock: "Unlock preview",
      reset: "Preview recovery",
      "sign-in": "Sign in",
      "sign-up": "Sign up",
      verification: "Send code",
    })[props.specimen],
);
const layoutClass = computed(() => ({ basic: "bg-surface-inset", cover: "bg-linear-to-br from-primary via-primary to-info", illustration: "bg-linear-to-br from-surface-inset via-primary/10 to-secondary/20" })[props.layout]);
const submit = (): void => {
  submitted.value = true;
};
</script>

<template>
  <div :class="layoutClass" class="relative grid min-h-[34rem] overflow-hidden rounded-2xl p-4 sm:p-8" :data-auth-layout="layout">
    <div v-if="layout === 'illustration'" aria-hidden="true" class="pointer-events-none absolute inset-y-0 right-0 hidden w-1/2 place-items-center lg:grid">
      <div class="grid size-64 rotate-6 place-items-center rounded-[3rem] border border-primary/20 bg-primary/10 text-primary shadow-xl"><Icon decorative name="i-ph-shield-check" class="size-28" /></div>
    </div>
    <div v-if="layout === 'cover'" class="relative mb-6 max-w-xl text-white">
      <p class="text-sm font-semibold tracking-[0.2em] uppercase">Argon identity reference</p>
      <h2 class="mt-2 text-3xl font-semibold">A protected preview, not an authentication endpoint.</h2>
    </div>
    <div class="relative grid place-items-center" :class="layout === 'illustration' ? 'lg:w-1/2' : ''">
      <Card v-if="isError" as="section" class="grid w-full max-w-xl place-items-center gap-5 text-center" padding="lg" variant="glass">
        <span class="text-7xl font-semibold tracking-tight text-primary">{{ errorStatus }}</span>
        <div class="grid gap-2">
          <h3 class="text-2xl font-semibold text-content-heading">{{ specimen === "error-500" ? "Something went wrong" : "Page not found" }}</h3>
          <p class="text-pretty text-content-body">{{ specimen === "error-500" ? "The preview records a recoverable server-error state without simulating an actual failure." : "This inert status page keeps keyboard focus on a safe route back to the gallery." }}</p>
        </div>
        <Button class="dark:[--ui-tone:var(--ui-color-primary-700)]" :label="actionLabel" @click="submit" />
        <UAlert v-if="submitted" color="info" description="Navigation is intentionally disabled inside the specimen." title="Preview only" variant="subtle" />
      </Card>

      <Card v-else as="section" class="grid w-full max-w-md gap-5" padding="lg" variant="glass">
        <header class="grid justify-items-center gap-3 text-center">
          <div class="grid size-14 place-items-center rounded-full bg-primary/10 text-primary"><Icon decorative :name="specimen === 'lock' ? 'i-ph-lock-key' : specimen === 'verification' ? 'i-ph-device-mobile' : 'i-ph-user-circle'" size="lg" /></div>
          <div>
            <h3 class="text-2xl font-semibold text-content-heading">{{ title }}</h3>
            <p class="mt-1 text-pretty text-content-body">{{ description }}</p>
          </div>
        </header>

        <form class="grid gap-4" @submit.prevent="submit">
          <FormInput v-if="specimen === 'sign-up'" v-model="name" autocomplete="off" label="Name" name="reference-auth-name" required />
          <FormInput
            v-if="specimen === 'reset' || specimen === 'sign-in' || specimen === 'sign-up'"
            v-model="email"
            autocomplete="off"
            :error="specimen === 'sign-up' && layout === 'illustration' ? 'This address already belongs to a preview profile.' : undefined"
            label="Email"
            name="reference-auth-email"
            required
            type="email"
          />
          <FormInput v-if="specimen === 'lock' || specimen === 'sign-in' || specimen === 'sign-up'" v-model="password" autocomplete="off" label="Password" name="reference-auth-password" required type="password" />
          <FormInput v-if="specimen === 'verification'" v-model="verificationCode" autocomplete="off" inputmode="numeric" label="Verification code" maxlength="6" name="reference-auth-code" placeholder="000000" required />
          <FormCheckbox v-if="specimen === 'sign-in'" v-model="remember" label="Remember this preview" name="reference-auth-remember" />
          <FormCheckbox v-if="specimen === 'sign-up'" v-model="terms" label="I agree to the preview terms" name="reference-auth-terms" />
          <Button block class="dark:[--ui-tone:var(--ui-color-primary-700)]" :label="actionLabel" type="submit" />
        </form>

        <UAlert v-if="submitted" color="success" description="The interaction stopped inside the gallery; no Fortify route was called and no account data changed." title="Inert state confirmed" variant="subtle" />
      </Card>
    </div>
  </div>
</template>
