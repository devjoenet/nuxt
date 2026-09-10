<script setup lang="ts">
import { Alert } from "@/components/Ui/Alert";
import { Card } from "@/components/Ui/Card";
import { FormInput } from "@/components/Ui/FormInput";
import { FormSelect } from "@/components/Ui/FormSelect";
import { ref } from "vue";
const step = ref(0);
const finished = ref(false);
const steps = ["About", "Account", "Address"];
const firstName = ref("Sayo");
const lastName = ref("Kravits");
const email = ref("sayo@example.test");
const interests = ref(["Design"]);
const street = ref("Main Street");
const city = ref("San Francisco");
const country = ref("United States");
function next(): void {
  if (step.value < 2) step.value++;
  else finished.value = true;
}
</script>
<template>
  <Card class="mx-auto w-full max-w-3xl" variant="outline"
    ><template #header
      ><h3 class="text-center text-2xl font-semibold text-content-heading">Build Your Profile</h3>
      <p class="text-center text-content-subtle">This information will let us know more about you.</p></template
    >
    <ol class="mb-6 grid grid-cols-3 gap-3">
      <li v-for="(label, index) in steps" :key="label" :aria-current="index === step ? 'step' : undefined" :class="['border-b-2 pb-3 text-center text-sm', index === step ? 'border-primary font-semibold text-primary' : 'border-content-border text-content-subtle']">
        {{ index + 1 }}. {{ label }}
      </li>
    </ol>
    <form class="grid gap-5" @submit.prevent="next">
      <h4 aria-live="polite" class="text-lg font-semibold text-content-heading">{{ steps[step] }}</h4>
      <div v-if="step === 0" class="grid gap-4 sm:grid-cols-2"><FormInput v-model="firstName" label="First name" required /><FormInput v-model="lastName" label="Last name" required /><FormInput v-model="email" class="sm:col-span-2" label="Email" type="email" required /></div>
      <fieldset v-else-if="step === 1" class="grid gap-4">
        <legend class="mb-4 text-content-heading">What are you doing?</legend>
        <UCheckboxGroup v-model="interests" :items="['Design', 'Code', 'Develop']" />
      </fieldset>
      <div v-else class="grid gap-4 sm:grid-cols-2"><FormInput v-model="street" label="Street address" required /><FormInput v-model="city" label="City" required /><FormSelect v-model="country" label="Country" :items="['United States', 'United Kingdom', 'Germany']" /></div>
      <div class="flex justify-between gap-3"><UButton label="Previous" color="neutral" variant="outline" :disabled="step === 0" @click="step--" /><UButton :label="step === 2 ? 'Create demo profile' : 'Next'" type="submit" /></div>
      <Alert v-if="finished" title="Demo profile created" description="Your profile preview is saved only for this visit." tone="success" variant="outline" />
    </form>
  </Card>
</template>
